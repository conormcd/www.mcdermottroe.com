<?php

/**
 * Collator of URLs and metadata for the entire site.
 *
 * @author Conor McDermottroe <conor@mcdermottroe.com>
 */
class GoogleSiteMapModel
extends Model
{
    /**
     * This will never be used because the output is entirely XML.
     *
     * @return string A description of this object.
     */
    public function description() {
        return 'A sitemap for Google.';
    }

    /**
     * A hash of the output, for caching purposes.
     *
     * @return string A hash of the data which will be formatted in XML.
     */
    public function eTag() {
        return md5(var_export($this->urls(), true));
    }

    /**
     * All of the URLs that will be in the sitemap.
     *
     * @return array A list of the URLs that will be included in the sitemap.
     */
    public function urls() {
        ini_set("memory_limit", -1);
        $tech = new TechModel();
        $about = new AboutModel();
        return array_merge(
            $this->frontPageURLs(),
            $this->blogURLs(),
            $this->photoURLs(),
            array(
                array(
                    'loc' => $tech->canonicalLink(),
                    'lastmod' => $tech->updatedTime(),
                ),
                array(
                    'loc' => $about->canonicalLink(),
                    'lastmod' => $about->updatedTime(),
                ),
            )
        );
    }

    /**
     * The URLs for the front page.
     *
     * @return array All the URLs that are valid for the front page.
     */
    public function frontPageURLs() {
        $urls = array();
        for ($i = 1; $i <= 100; $i++) {
            try {
                $model = new FrontPageModel($i);
                if (count($model->entries()) > 0) {
                    $urls[] = array(
                        'loc' => $model->canonicalLink(),
                        'lastmod' => $model->updatedTime(),
                    );
                }
            } catch (Exception $e) {
                break;
            }
        }
        return $urls;
    }

    /**
     * The URLs for the blog.
     *
     * @return array All the URLs for the blog.
     */
    public function blogURLs() {
        $urls = array();

        // Pages of blog posts
        for ($i = 1; $i <= 100; $i++) {
            try {
                $model = new BlogModel(null, null, null, null, $i, null);
                if (count($model->entries()) > 0) {
                    $urls[] = array(
                        'loc' => $model->canonicalLink(),
                        'lastmod' => $model->updatedTime(),
                    );
                }
            } catch (Exception $e) {
                break;
            }
        }

        // Individual blog posts.
        $model = new BlogModel(null, null, null, null, 1, 1000);
        foreach ($model->entries() as $blog_post) {
            $urls[] = array(
                'loc' => $blog_post->canonicalLink(),
                'lastmod' => $blog_post->updatedTime(),
            );
        }

        return $urls;
    }

    /**
     * The URLs for all of the photo pages.
     *
     * @return array All the URLs for the photo pages.
     */
    public function photoURLs() {
        $urls = array();

        // Album listing
        $photos = new PhotosModel();
        $urls[] = array(
            'loc' => $photos->canonicalLink(),
            'lastmod' => $photos->updatedTime(),
        );

        // The albums and photos themselves.
        $albums = $photos->albums();
        foreach ($albums as $album) {
            $album_name = $album->slug();
            $page = new PhotosModel($album_name);
            $num_pages = $page->numPages();
            $lastmod = $page->updatedTime();
            for ($i = 1; $i <= $num_pages; $i++) {
                $urls[] = array(
                    'loc' => 'http://www.mcdermottroe.com' .
                             $page->generateLink($album_name, $i, -1),
                    'lastmod' => $lastmod,
                );
            }

            foreach ($photos->photos($album_name) as $photo) {
                $urls[] = array(
                    'loc' => $photo->canonicalLink(),
                    'lastmod' => $lastmod,
                );
            }
        }

        return $urls;
    }
}

?>
