<?php

	// Check if the 'rss' query parameter is set
	if (isset($_GET['rss'])) {
		// Output the RSS feed
	include 'config/config.php';
	include 'rss_generator.php';
		exit; // Stop further execution
	}

	// Enable Gzip compression for HTML output
	if (substr_count($_SERVER['HTTP_ACCEPT_ENCODING'], 'gzip')) { // Check if the client supports Gzip compression
		ob_start("ob_gzhandler"); // Start output buffering with Gzip compression handler
	} else {
		ob_start(); // Start regular output buffering
	}

    include 'config/config.php';
	
	if(isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') {
		$protocol = "https://";
	} else {
		$protocol = "http://";
	}
	$CurrentDomainURL = $protocol . $_SERVER['HTTP_HOST'];

	
    if ($CurrentDomainURL != $WebsiteURL) {
        header("Location: " . $WebsiteURL);
        die();
    }
	
/****************************************************/
/* Protect the entire site from random URL snooping */
	// Parse the requested URL
	$requestUri = $_SERVER['REQUEST_URI']; /* This line retrieves the requested URI from the $_SERVER superglobal array. $_SERVER['REQUEST_URI'] contains the URI (Uniform Resource Identifier) that was requested by the client. */
	
	$requestedPage = trim(parse_url($requestUri, PHP_URL_PATH), '/'); /* This line parses the requested URI using the parse_url() function and extracts the path component using the PHP_URL_PATH constant. The trim() function is then used to remove any leading or trailing slashes from the path. */
	if ($requestedPage == "") { $requestedPage = "home"; }
	//echo $requestedPage; /* This line is commented out and is not executed. It's purpose is to echo/print the value of $requestedPage for testing. */
	
	
	/* Generate list of potential target pages */
	/* The purpose of this code block is to generate a list of files in the pages folder (and subfolders). If the requested page is not in that list, send it to 404. This can help eliminate some potential attack vectors*/
	/* This code defines a recursive function listFiles() that traverses through directories and subdirectories, collecting all file paths. Then, it checks if the requested page exists in the list of files. If it doesn't exist, it sets $pagename to "404". */
	function listFiles($dir) {
		$files = [];
		$contents = scandir($dir);
		
		foreach ($contents as $content) {
			if ($content != '.' && $content != '..') {
				$path = $dir . '/' . $content;
				if (is_dir($path)) {
					$files = array_merge($files, listFiles($path));
				} else {
					$files[] = $path;
				}
			}
		}
		
		return $files;
	}

	// Get the list of files in the pages directory and its subdirectories
	$files = listFiles("pages");

	/* Sanity testing to make sure this is working the way we want */
	
	//	echo '<pre>'; print_r($files); echo '</pre>';
	//	echo "pages/" . $requestedPage . ".md";
	

	// Check if the requested page exists in the list of files. If it doesn't, then force it onto a 404 page.
	if (!in_array("pages/" . $requestedPage . ".html", $files)) {
		header("Location: " . $protocol . $_SERVER['HTTP_HOST'] . "/404");
		die();
	}
	/* END Generate list of potential target pages */
	
	
	/* This is a catchall, just in case. There is also another catchall in the config file, just in case this one doesn't do the trick. This is nowhere near as sophisticated as the approach above, but it still could potentially catch things that fell through the cracks */
	if (!file_exists("pages/" . $requestedPage .".html")) { 
		header("Location: " . $protocol . $_SERVER['HTTP_HOST'] . "/404");
		die();
	}
/* END Protect the entire site from random URL snooping */
/********************************************************/


	if ($requestedPage =="") { 
	/* This block of code determines the name of the requested page based on the value of $requestedPage. If $requestedPage is empty (i.e., the root URL is requested), the page name is set to "home" and redirected via 301 to it. Otherwise, the page name is set to the value of $requestedPage. */
		$pagename = "home";
	} else {
		$pagename = strtolower($requestedPage);
	}
	/* In summary, this code retrieves the requested URI, extracts the path component, and determines the name of the requested page based on the path. If no specific page is requested (e.g., accessing the root URL), it sets the page name to "home". */
	
	
	if ($enableHTMLCacheServe == true) { include 'required/top-cache.php'; }
	include 'required/initialize-markdown-parser.php';
	include 'required/shortcode-parser.php';
	include 'required/helperfunctions.php';
	include 'required/vitalfunctions.php';
	include 'themes/' . $theme . '/header.php';
	if ($enableHTMLCacheServe == true) { include 'required/bottom-cache.php'; }
	
	// Flush the output buffer and send the content to the client
	ob_end_flush();
?>