<?php

require_once dirname(dirname(dirname(__DIR__))) . '/lib/autoloader.php';
require_once __DIR__ . '/FactoryTestCase.php';

/**
 * Tests for Flickr.
 *
 * @author Conor McDermottroe <conor@mcdermottroe.com>
 */
class FlickrTest
extends FactoryTestCase
{
    private $_flickr;

    /**
     * Pre-test setup
     *
     * @return void
     */
    public function setUp() {
        parent::setUp();
        $this->class = get_class();

        $this->_flickr = Flickr::getInstance();
    }

    /**
     * Test Flickr.getAlbums
     *
     * @return void
     */
    public function testGetAlbums() {
        $this->fakePeopleFindByUsername();
        $this->fakePhotosetsGetList();

        $albums = $this->_flickr->getAlbums();
        $this->assertNotNull($albums);
        foreach ($albums as $album) {
            $this->assertInstanceOf('PhotoAlbumModel', $album);
        }
    }

    /**
     * Test getting a failure from flickr.photosets.getList
     *
     * @return void
     */
    public function testGetAlbumsFail() {
        $this->fakePeopleFindByUsername();
        $this->fakePhotosetsGetList(
            array(
                'stat' => 'fail',
                'message' => 'Fake failure',
                'code' => 1
            )
        );

        $flickr = $this->_flickr;
        $exception = $this->assertException(
            function () use ($flickr) {
                $flickr->getAlbums();
            }
        );
        $this->assertEquals('Fake failure', $exception->getMessage());
        $this->assertEquals(404, $exception->getCode());
    }

    /**
     * Test Flickr.getAlbum()
     *
     * @return void
     */
    public function testGetAlbum() {
        $this->fakePeopleFindByUsername();
        $this->fakePhotosetsGetList();
        foreach ($this->_flickr->getAlbums() as $album) {
            $this->assertEquals(
                $album,
                $this->_flickr->getAlbum($album->slug())
            );
        }
    }

    /**
     * Test Flickr.getAlbum handles exceptions correctly.
     *
     * @return void
     */
    public function testGetAlbumMissing() {
        $this->fakePeopleFindByUsername();
        $this->fakePhotosetsGetList();
        $flickr = $this->_flickr;
        $exception = $this->assertException(
            function () use ($flickr) {
                $flickr->getAlbum('Does not exist.');
            }
        );
        $this->assertEquals(404, $exception->getCode());
    }

    /**
     * Test Flickr.getPhotos
     *
     * @return void
     */
    public function testGetPhotos() {
        $this->fakePeopleFindByUsername();
        $this->fakePhotosetsGetList();
        $this->fakePhotosetsGetPhotos();

        $albums = $this->_flickr->getAlbums();
        $this->assertNotEmpty($albums);

        $photos = $this->_flickr->getPhotos($albums[0]);
        $this->assertNotNull($photos);
        foreach ($photos as $photo) {
            $this->assertInstanceOf('PhotoModel', $photo);
        }
    }

    /**
     * Fake out flickr.people.findByUsername.
     *
     * @return void
     */
    private function fakePeopleFindByUsername() {
        $this->fakeFlickrAPI(
            'flickr.people.findByUsername',
            array('username' => $_ENV['FLICKR_API_USER']),
            function () {
                return JSON::encode(
                    array(
                        'stat' => 'ok',
                        'user' => array('nsid' => 1234567),
                    )
                );
            }
        );
    }

    /**
     * Fake out flickr.photosets.getList.
     *
     * @param array $return The data to return from the call.
     *
     * @return void
     */
    private function fakePhotosetsGetList($return = null) {
        if ($return === null) {
            $return = array(
                'stat' => 'ok',
                'photosets' => array(
                    'photoset' => array(
                        array(
                            'id' => 'abcd1234',
                            'title' => array('_content' => 'Fake set'),
                            'description' => array('_content' => 'Fake'),
                            'date_create' => time(),
                            'primary' => 123456
                        )
                    ),
                ),
            );
        }

        $this->fakeFlickrAPI(
            'flickr.photosets.getList',
            array('user_id' => 1234567),
            function () use ($return) {
                return JSON::encode($return);
            }
        );
    }

    /**
     * Fake out flickr.photosets.getPhotos.
     *
     * @param array $return The data to return from the call.
     *
     * @return void
     */
    private function fakePhotosetsGetPhotos($return = null) {
        if ($return === null) {
            $return = array(
                'stat' => 'ok',
                'photoset' => array(
                    'id' => 'abcd1234',
                    'primary' => 123456,
                    'photo' => array(
                        array(
                            'id' => 123456,
                            'title' => 'A fake photo',
                            'description' => array(
                                '_content' => 'Fake photo description'
                            ),
                            'url_q' => 'fake',
                            'url_o' => 'fake',
                            'url_c' => 'fake',
                        )
                    ),
                ),
            );
        }

        $this->fakeFlickrAPI(
            'flickr.photosets.getPhotos',
            array(
                'photoset_id' => 'abcd1234',
                'extras' => join(
                    '%2C',
                    array('url_o', 'url_q', 'url_c', 'url_z', 'url_m')
                )
            ),
            function () use ($return) {
                return JSON::encode($return);
            }
        );
    }

    /**
     * Fake out a Flickr API call.
     *
     * @param string $method   The Flickr API method to fake.
     * @param array  $params   Any extra parameters for the request.
     * @param array  $response The response to feed back to the requester.
     *
     * @return void
     */
    private function fakeFlickrAPI($method, $params, $response) {
        $url  = "https://api.flickr.com/services/rest/";
        $url .= "?api_key={$_ENV['FLICKR_API_KEY']}";
        $url .= "&format=json&nojsoncallback=1";
        $url .= "&method=$method";
        foreach ($params as $k => $v) {
            $url .= "&$k=$v";
        }
        FakeHTTPClient::addResponse("/" . preg_quote($url, '/') . "/", $response);
    }
}

?>
