<?php    

require('scrapeUtil.inc');

// set up some vars
$tags = array('debug' => true,
              'images' => array('start' => '<div class="container photo-details-base">', 
                                'end' => '<td align="right" class="nav-links">Previous'),
              'pages' => array('start' => '<td class="range-of-total">',
                               'end' => '</span>')
              );
$yelpID = '2IQYwha0_SHemfYwuQi6Xg#ccirF51WzYOl4uEwLGPBLw';
$mainUrl = 'http://www.yelp.com/biz_photos/the-french-laundry-yountville-2?select=' . $yelpID;

function mylog($str, $debug) {
    if ($debug) {
        error_log($str);
    }
}

function getImages($scraper, $pdata, $tags) {
    // images are between these tags
    $images = $scraper->getTagData($pdata,
                                   $tags['images']['start'],
                                   $tags['images']['end']);

    // the img tag
    $pics = '<div class="photo-box biz-photo-box pb-ms">';
    $res = explode($pics, $images);
    foreach ($res as $r) {
        $nr = $scraper->getTagData($r, '<a href=', '</a>');
        $nimg = strpos($nr, '<img alt=');
        $imgTag = substr($nr, $nimg); // this gets us the image tag, since it ends at </a>
        $nsrc = strpos($imgTag, 'src="');
        $nsrcJpg = strpos($imgTag, 'ms.jpg');
        $imgName = substr($imgTag, $nsrc, $nsrcJpg - $nsrc);
        mylog($imgName . 'l.jpg', $tags['debug']);
        echo '<img ' . $imgName . 'l.jpg">';
    }
}

$scraper = new scrapeUtils();
mylog('START scraping', $tags['debug']);

// need to get the number of pages first, so call this outside the loop the first time
$scraper->curl($mainUrl);
$pdata = $scraper->pageData();
// number of pages tags
$imgCount = $scraper->getTagData($pdata,
                          $tags['pages']['start'],
                          $tags['pages']['end']);
$imgCountAr = explode(' ', $imgCount);
$npages = $imgCountAr[count($imgCountAr) - 1] / 100;

getImages($scraper, $pdata, $tags);

for ($n = 2; $n < $npages; $n++) {
    $murl = $mainUrl . '&page=' . $n;
    $scraper->curl($murl);
    $pdata = $scraper->pageData();
    getImages($scraper, $pdata, $tags);
 }

mylog('npages: ' . $npages, $tags['debug']);
mylog('** DONE **', $tags['debug']);
?>