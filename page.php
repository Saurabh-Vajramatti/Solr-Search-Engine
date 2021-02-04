<?php


// make sure browsers see this page as utf-8 encoded HTML
header('Content-Type: text/html; charset=utf-8');

$limit = 10;
$query = isset($_REQUEST['q']) ? $_REQUEST['q'] : false;
$results = false;

if ($query)
{
  // The Apache Solr Client library should be on the include path
  // which is usually most easily accomplished by placing in the
  // same directory as this script ( . or current directory is a default
  // php include path entry in the php.ini)
  require_once('./solr-php-client-master/Apache/Solr/Service.php');

  // create a new solr service instance - host, port, and webapp
  // path (all defaults in this example)
  $solr = new Apache_Solr_Service('localhost', 8983, '/solr/myexample');

  // if magic quotes is enabled then stripslashes will be needed
  if (get_magic_quotes_gpc() == 1)
  {
    $query = stripslashes($query);
  }
  $arr = explode(" ", $query);
  // in production code you'll always want to use a try /catch for any
  // possible exceptions emitted  by searching (i.e. connection
  // problems or a query parsing error)
  try
  {
  	if($_GET['method'] == "lucene")
  	{
    	$results = $solr->search($query, 0, $limit);
  	}
  	else
  	{
  		$additionalParameters = array('sort' => 'pageRankFile desc');
  		$results = $solr->search($query, 0, $limit, $additionalParameters);
  	}
  }
  catch (Exception $e)
  {
    // in production you'd probably log or email this error to an admin
    // and then show a special message to the user but for this example
    // we're going to show the full exception
    die("<html><head><title>SEARCH EXCEPTION</title><body><pre>{$e->__toString()}</pre></body></html>");
  }
}

?>
<html>
  <head>
    <title>PHP Solr Client Example</title>
  </head>
  <body>
    <form  accept-charset="utf-8" method="get">
      <label for="q">Search : </label>
      <input id="q" name="q" type="text" value="<?php echo htmlspecialchars($query, ENT_QUOTES, 'utf-8'); ?>"/>
      <br/>
      <input type="radio" name="method" value="lucene"<?php if(isset($_REQUEST['method']) && $_REQUEST['method'] == 'lucene') {echo 'checked="checked"';} ?>> Lucene
	      <input type="radio" name="method" value="pagerank"<?php if(isset($_REQUEST['method']) && $_REQUEST['method'] == 'pagerank') {echo 'checked="checked"';} ?>> Page Rank
      <br/>
      <input type="submit"/>
    </form>
<?php

// display results
if ($results)
{
  $total = (int) $results->response->numFound;
  $start = min(1, $total);
  $end = min($limit, $total);
?>
    <div>Results <?php echo $start; ?> - <?php echo $end;?> of <?php echo $total; ?>:</div>
    <ol>
<?php
  // iterate result documents
  $csv_map = array_map('str_getcsv', file('URLtoHTML_latimes_news.csv'));
  // foreach ($results->response->docs as $doc)
  // {
    for ($in = 0; $in < count($results->response->docs); $in++)
    {

	  $id = $results->response->docs[$in]->id;
  	$title = $results->response->docs[$in]->title;
  	$url = $results->response->docs[$in]->og_url;
  	$desc = $results->response->docs[$in]->og_description;
  	if($results->response->docs[$in]->og_description == "" || $results->response->docs[$in]->og_description == null)
  	{
  		$results->response->docs[$in]->og_description = "N/A";
	}
	if($results->response->docs[$in]->title == "" || $results->response->docs[$in]->title == null)
  	{
  		$results->response->docs[$in]->title = "N/A";
	}
	if($results->response->docs[$in]->og_url == "" || $results->response->docs[$in]->og_url == null)
	{
    for ($x = 0; $x < count($csv_map); $x++)
    {
			$cmp = "/Users/saurabhvajramatti/Saurabh Vajramatti USC/IR/HW4/Info/latimes/" . $csv_map[$x][0];
			if($id == $cmp)
			{
				$url = $csv_map[$x][1];
				unset($csv_map[$x]);
				break;
			}
		}
	}
	echo "Title : <a href = '$url'>$title</a></br>";
	echo	"URL : <a href = '$url'>$url</a></br>";
	echo	"ID : $id</br>";
	echo	"Description : $desc </br></br>";
	}
}
?>
	</body>
</html>