<!DOCTYPE html>
<html>
	<head>
		<title>Tag Test</title>
	</head>
	<body>
<?php
	require_once './htmlpurifier/library/HTMLPurifier.auto.php';
	
	$config = HTMLPurifier_Config::createDefault();
	$purifier = new HTMLPurifier($config);

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
include_once 'HtmlTagStack.php';
$test = array();

$test[] = '<h1>Test 1</h1>
<div>
	<p>Tests no errors, and no styling</p>
	<p>This is test 1, <br />line one</p>
	<p>This is test 1, line two</p>
	<p>The test continues.</p>
</div>';

$test[] = '<h1>Test 2</h1>
<div>
	<p>Tests formatting errors, missing end paragraph</p>
	<p>This is test 2, line one</p>
	<p>This is test 2, line two</p>
	<p>The test continues.
</div>';

$test[] = '<h1>Test 3</h1>
<div>
	<p>Tests no errors, and with styling</p>
	<p>This is <em>test 3</em>, line one</p>
	<p>This is <strong>test 3</strong>, line two</p>
	<p>The test continues.</p>
</div>';

$test[] = '<h1>Test 4</h1>
<div>
	<p>Tests formatting errors, em breaks out of the paragraph</p>
	<p>This is <em>test 4</em>, line one</p>
	<p>This is <em><strong>test 4</strong>, line two</p>
	<p>The test</em> continues.</p>
</div>';

$test[] = '<h1>Test 5</h1>
<div>
	<p>Tests formatting errors, em breaks out of the paragraph, and em is not nested inside strong, Docx can return that.</p>
	<p>This is <em>test 5</em>, line one</p>
	<p>This is <strong><em>test 5</strong>, line</em> two</p>
	<p>The test</strong> continues.</p>
</div>';

$idx = 0;
foreach ($test as $html) {
	$idx++;
	echo "<h3>Test $idx</h3>\n";
	echo "<p><strong>Original:</strong></p>";
	echo "\n<pre>";
	echo htmlentities($html) . "\n<strong>tag_sanitizer</strong>:\n";
	echo htmlentities(tag_sanitizer($html));
	echo "</pre>\n";

	echo "<p><strong>Purifier:</strong></p>";
	$clean_html = $purifier->purify($html);
	$clean_html_sanitized = $purifier->purify(tag_sanitizer($html));
	echo "\n<pre>";
	echo htmlentities($clean_html) . "\n<strong>tag_sanitizer</strong>:\n";
	echo htmlentities($clean_html_sanitized);
	echo "</pre>\n";
}
?>
</body>
</html>