<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use MongoDB\Client;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Route;


class TestController extends Controller
{
    public function testMongoDBConnection()
    {
        $client = new Client(); // Create a new MongoDB client
        $collection = $client->your_database_name->your_collection_name; // Replace with your database and collection names
        $data = $collection->find();
        dd($data);
    }

    public function initializeMongoDB()
    {
        // Run migrations and seeders
        Artisan::call('migrate');
        Artisan::call('db:seed', ['--class' => 'UrlsSeeder']);

        return 'MongoDB initialized with data';
    }

    public function testCrawlerController(Request $request)
    {
        // URL and depth for testing
        $url = 'https://example.com';
        $depth = 2; // Set the desired depth for testing
        // Send a POST request to the crawl endpoint
        $response = Route::dispatch(Request::create('/crawl', 'POST', [
            'url' => $url,
            'depth' => $depth,
        ]));

        // Check the response status and content
        if ($response->getStatusCode() === 200) {
            // Display the response message
            return response()->json(['message' => 'Crawling completed.']);
        } else {
            // Display the error message
            return response()->json(['error' => 'Crawling failed.'], JsonResponse::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
