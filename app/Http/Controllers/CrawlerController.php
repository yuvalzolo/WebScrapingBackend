<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use GuzzleHttp\Client;
use App\Models\Url;
use GuzzleHttp\Exception\TooManyRedirectsException;
use Illuminate\Http\JsonResponse;
use SplQueue;

class CrawlerController extends Controller
{
    public function crawl(Request $request)
    {
        $url = $request->input('url');
        $depth = $request->input('depth');

        // Initialize the Guzzle HTTP client
        try {
            $client = new Client();

            // Start the crawling process using a queue
            $queue = new SplQueue();
            $queue->enqueue(['url' => $url, 'depth' => $depth]);

            while (!$queue->isEmpty()) {
                $item = $queue->dequeue();
                $this->crawlPage($client, $item['url'], $item['depth'], $url, $queue);
            }

            // Fetch URLs with the specified initial_url
            $urls = Url::where('initial_url', $url)->get();
            return response()->json(['message' => 'Crawling completed.', 'urls' => $urls]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], JsonResponse::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    private function crawlPage(Client $client, $source_url, $depth, $initial_url, $queue, $redirected_urls = [])
    {
        if (in_array($source_url, $redirected_urls)) {
            // Skip processing this URL to avoid redirection loops
            return;
        }
        $redirected_urls[] = $source_url;

        // Perform a GET request to the URL
        $response = null;
        try {
            $response = $client->get($source_url, [
                'verify' => false,
                'allow_redirects' => [
                    'max' => 5, // Maximum number of redirects to follow
                    'strict' => true,
                    'referer' => true,
                    'track_redirects' => true,
                ],
            ]);
        } catch (TooManyRedirectsException $e) {
            // Handle the exception
            $errorMessage = 'Too many redirects occurred for URL: ' . $source_url;
            \Illuminate\Support\Facades\Log::error($errorMessage);

            // Set $response to null or perform any other necessary action
            $response = null;
        }

        // Check if the response status code is successful
        if ($response && $response->getStatusCode() >= 200 && $response->getStatusCode() < 300) {
            $html = $response->getBody()->getContents();

            preg_match_all('/<a\s+(?:[^>]*?\s+)?href="([^"]*)"/', $html, $matches);
            $urls = $matches[1];

            // Normalize and validate URLs and enqueue for processing
            foreach ($urls as $url) {
                $normalizedUrl = filter_var($url, FILTER_VALIDATE_URL);
                if ($normalizedUrl) {
                    Url::firstOrCreate([
                        'url' => $normalizedUrl
                    ], [
                        'source_url' => $source_url,
                        'initial_url' => $initial_url
                    ]);

                    // Enqueue for crawling if the depth limit is not reached
                    if ($depth > 1) {
                        $queue->enqueue(['url' => $normalizedUrl, 'depth' => $depth - 1]);
                    }
                }
            }
        }
    }

    public function getUrls()
    {
        $urls = Url::all();
        return response()->json(['urls' => $urls]);
    }

    public function getRelatedUrls(Request $request)
    {
        $initialUrl = $request->query('initial_url');
        $relatedUrls = Url::where('initial_url', $initialUrl)->get();
        return response()->json(['related_urls' => $relatedUrls]);
    }
}
