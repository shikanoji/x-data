<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Client;
use Symfony\Component\DomCrawler\Crawler;

class DataCrawler extends Controller
{
    //
    private $client;

    public function __construct()
    {
        $this->client = new Client([
                'timeout'   => 10,
                'verify'    => false
            ]);
    }
    /**
     * Content Crawler
     */
    public function getCrawlerContent()
    {
        try {
            $response = $this->client->get('<URL>'); // URL, where you want to fetch the content
            // get content and pass to the crawler
            $content = $response->getBody()->getContents();
            $crawler = new Crawler( $content );
            
            $_this = $this;
            $data = $crawler->filter('div.card--post')
                            ->each(function (Crawler $node, $i) use($_this) {
                                return $_this->getNodeContent($node);
                            }
                        );
            dump($data);
            
        } catch ( Exception $e ) {
            echo $e->getMessage();
        }
    }
    /**
     * Check is content available
     */
    private function hasContent($node)
    {
        return $node->count() > 0 ? true : false;
    }
    /**
     * Get node values
     * @filter function required the identifires, which we want to filter from the content.
     */
    private function getNodeContent($node)
    {
        $array = [
            'title' => $this->hasContent($node->filter('.post__content h2')) != false ? $node->filter('.post__content h2')->text() : '',
            'content' => $this->hasContent($node->filter('.post__content p')) != false ? $node->filter('.post__content p')->text() : '',
            'author' => $this->hasContent($node->filter('.author__content h4 a')) != false ? $node->filter('.author__content h4 a')->text() : '',
            'featured_image' => $this->hasContent($node->filter('.post__image a img')) != false ? $node->filter('.post__image a img')->eq(0)->attr('src') : ''
        ];
        return $array;
    }
}
