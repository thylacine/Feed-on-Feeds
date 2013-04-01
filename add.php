<?php
/*
 * This file is part of FEED ON FEEDS - http://feedonfeeds.com/
 *
 * add.php - displays form to add a feed
 *
 *
 * Copyright (C) 2004-2007 Stephen Minutillo
 * steve@minutillo.com - http://minutillo.com/steve/
 *
 * Distributed under the GPL - see LICENSE
 *
 */

include('header.php');

$url = isset($_POST['rss_url']) ? $_POST['rss_url'] : NULL;
if (! isset($url)) {
    $url = isset($_GET['rss_url']) ? $_GET['rss_url'] : NULL;
}

$opml = isset($_POST['opml_url']) ? $_POST['opml_url'] : NULL;
$file = isset($_POST['opml_file']) ? $_POST['opml_file'] : NULL;
$unread = isset($_POST['unread']) ? $_POST['unread'] : NULL;

$feeds = array();

if ($url)
    $feeds[] = $url;

if ($opml) {
    $sfile = new SimplePie_File($opml);
    if ( ! $sfile->success) {
        echo "Cannot open " . htmlentities($opml) . "<br>";
        return false;
    }

    $content = $sfile->body;
    $feeds = fof_opml_to_array($content);
}

if ( ! empty($_FILES['opml_file']) && ! empty($_FILES['opml_file']['tmp_name'])) {
    if (($content = file_get_contents($_FILES['opml_file']['tmp_name'])) === false) {
        echo "Cannot open uploaded file '" . htmlentities($_FILES['opml_file']['name']) . "'<br>";
    } else {
        $feeds = fof_opml_to_array($content);
    }
}

$add_feed_url = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on') ? 'https' : 'http';
$add_feed_url .= "://" . $_SERVER["HTTP_HOST"] . $_SERVER["SCRIPT_NAME"];
?>

<div style="background: #eee; border: 1px solid black; padding: 1.5em; margin: 1.5em;">If your browser is cool, you can <a href='javascript:window.navigator.registerContentHandler("application/vnd.mozilla.maybe.feed", "<?php echo $add_feed_url ?>?rss_url=%s", "Feed on Feeds")'>register Feed on Feeds as a Feed Reader</a>.  If it is not cool, you can still use the <a href="javascript:void(location.href='<?php echo $add_feed_url ?>?rss_url='+escape(location))">FoF subscribe</a> bookmarklet to subscribe to any page with a feed.  Just add it as a bookmark and then click on it when you are at a page you'd like to subscribe to!</div>

<form method="post" action="opml.php">

<input type="submit" value="Export subscriptions as OPML">

</form>
<br>

<form method="post" name="addform" action="add.php" enctype="multipart/form-data">

When adding feeds, mark <select name="unread">
    <option value=all<?php if ($unread == "all") echo " selected" ?>>all</option>
    <option value=today<?php if ($unread == "today") echo " selected" ?>>today's</option>
    <option value=no<?php if ($unread == "no") echo " selected" ?>>no</option>
</select> items as unread<br><br>

RSS or weblog URL: <input type="text" name="rss_url" size="40" value="<?php echo htmlentities($url) ?>"><input type="Submit" value="Add a feed"><br><br>

OPML URL: <input type="hidden" name="MAX_FILE_SIZE" value="100000">

<input type="text" name="opml_url" size="40" value="<?php echo htmlentities($opml) ?>"><input type="Submit" value="Add feeds from OPML file on the Internet"><br><br>

<input type="hidden" name="MAX_FILE_SIZE" value="100000">
OPML filename: <input type="file" name="opml_file" size="40" value="<?php echo htmlentities($file) ?>"><input type="Submit" value="Upload an OPML file">

</form>

<?php
if (count($feeds)) {
    $idx = 0;
    $feedjson = array();
    foreach ($feeds as $feed) {
        $feedjson[] = json_encode(array('url' => $feed));
        echo "<div id=\"f_idx_" . $idx . "\">";
        echo "<span id=\"feed_title\">[$idx] (<a href=\"$feed\">feed URL</a>)</span> ";
        echo "<span id=\"feed_status\">waiting to add...</span>";
        echo "</div>\n";
        $idx++;
    }

    echo "<script>\nwindow.onload = ajaxadd;\nfeedslist = [" . implode(', ', $feedjson) . "];\n</script>\n";
}
echo '<br>';

include('footer.php');
?>
