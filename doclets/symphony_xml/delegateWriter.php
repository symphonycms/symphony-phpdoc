<?php

class DelegateWriter extends HTMLWriter {
		
	function getFiles($start_dir='.') {
		$files = array();
		if (is_dir($start_dir)) {
			$fh = opendir($start_dir);
			while (($file = readdir($fh)) !== false) {
				if (strpos($file, '.') === 0) continue;
				$filepath = $start_dir . DIRECTORY_SEPARATOR . $file;
				if (is_dir($filepath)) {
					$files = array_merge($files, $this->getFiles($filepath));
				} else {
					array_push($files, $filepath);
				}
			}
			closedir($fh);
		} else {
			$files = FALSE;
		}
		return $files;
	}

	function delegateWriter(&$doclet) {
	
		parent::HTMLWriter($doclet);
		
		$rootDoc =& $this->_doclet->rootDoc();
		$phpdoctor =& $this->_doclet->phpdoctor();
		
		$path = $phpdoctor->_options['source_path'];
		$files = $this->getFiles($path);
		
		$doc = new DomDocument();
		$doc->preserveWhiteSpace = FALSE;
		$doc->formatOutput = TRUE;
		
		$dom_delegates = $doc->createElement('delegates');
		
		$delegates = array();
		
		foreach($files as $file) {
			$source = file_get_contents($file);
			
			$in_parsed_string = FALSE;
			$counter = 0;
            $lineNumber = 1;
            $commentNumber = 0;
            
			$tokens = token_get_all($source);			
			$numOfTokens = count($tokens);
			
            for ($key = 0; $key < $numOfTokens; $key++) {
                $token = $tokens[$key];

				$delegate = (object)array();
                
                if (!$in_parsed_string && is_array($token)) {
                    
                    $lineNumber += substr_count($token[1], "\n");

					$delegate->location = substr($file, strlen($path) + 1);
					$delegate->location_line = $lineNumber + 1;
                    
                    switch ($token[0]) {
                    
	                    case T_COMMENT: // read comment
	                    case T_ML_COMMENT: // and multiline comment (deprecated in newer versions)
	                    case T_DOC_COMMENT: // and catch PHP5 doc comment token too
							$comment = $token[1];
							if (preg_match("/@delegate/", $comment)) {
								
								$delegate->params = array();
								
								$tags = $this->processDocComment($comment);
								
								foreach($tags as $tag) {
									switch($tag['type']) {
										case 'text':
										$delegate->description = $tag['text'];
										break;
										case '@delegate':
										$delegate->name = $tag['text'];
										break;
										case '@param':
											
											$param_text = '';
											
											// from paramTag()
											
											$var = '';
											$text = '';
											
											$explode = preg_split('/[ \t]+/', $tag['text']);
											$type = array_shift($explode);
											if ($type) {
												$var = trim(array_shift($explode), '$');
												$text = join(' ', $explode);
											}
											if ($text != '') {
												$param_text = $var. '  - ' . $text;
												//parent::tag('@param', $this->_var.' - '.$text, $root);
											} else {
												$param_text = $var;
												//parent::tag('@param', NULL, $root);
											}
											
											
											// from HTMLWriter()
											$text = $param_text;
											$text = $this->__removeTextFromMarkup($text);

											$type_split = explode(' - ', $text);

											if (count($type_split) > 1) {
												$name = $type_split[0];
												array_shift($type_split);
												$description = join(' - ', $type_split);
											} else {
												$name = $text;
												$description = '';
											}
											
											$parameter = array(
												'description' => $description,
												'type' => trim($type),
												'name' => trim($name)
											);
											
											$delegate->params[] = $parameter;
										
										break;
									}
								}
								
								$delegates[$delegate->name] = $delegate;
								
							}
						break;
					}
				}
			}
			
		}
		
		ksort($delegates, SORT_STRING);
		//uksort($delegates, "strnatcasecmp"); 
		//$phpdoctor->message(var_dump($delegates));
		
		foreach($delegates as $delegate) {
			
			$dom_delegate = $doc->createElement('delegate');
			$dom_delegate->setAttribute('name', $delegate->name);
			
			$this->appendDescription($delegate->description, $doc, $dom_delegate);
			
			$dom_location = $doc->createElement('location', $delegate->location);
			$dom_location->setAttribute('line', $delegate->location_line);
			$dom_delegate->appendChild($dom_location);
			
			$dom_parameters = $doc->createElement('parameters');
			
			foreach($delegate->params as $param) {
				$dom_param = $doc->createElement('parameter');
				$dom_param->setAttribute('name', $param['name']);
				$dom_param->setAttribute('type', $param['type']);
				$this->appendDescription($param['description'], $doc, $dom_param);
				$dom_parameters->appendChild($dom_param);
			}
			
			$dom_delegate->appendChild($dom_parameters);
			$dom_delegates->appendChild($dom_delegate);
			
		}
		
		$doc->appendChild($dom_delegates);
		
		$this->_output = $doc->saveXML();
    	$this->_write('delegates.xml');

	
	}
	
	// modified from phpDoctor class
	function processDocComment($comment)
    {
		if (substr(trim($comment), 0, 3) != '/**') return array(); // not doc comment, abort
        
		$tags = array();
		
		$explodedComment = preg_split('/[\n|\r][ \r\n\t\/]*\*[ \t]*@/', "\n".$comment);
		$matches = array();
		preg_match_all('/^[ \t\/*]*\** ?(.*)[ \t\/*]*$/m', array_shift($explodedComment), $matches);
		if (isset($matches[1])) {
			$tags[] = array(
				'type' => 'text',
				'text' => trim(implode("\n", $matches[1]), " \n\r\t\0\x0B*/"),
			);
		}
		
		foreach ($explodedComment as $tag) { // process tags
            // strip whitespace, newlines and asterisks
            $tag = preg_replace('/(^[\s\n\r\*]+|\s*\*\/$)/m', ' ', $tag);
            $tag = preg_replace('/[\r\n]+/', '', $tag);
            $tag = trim($tag);
			
			$parts = preg_split('/\s+/', $tag);
			$name = isset($parts[0]) ? array_shift($parts) : $tag;
			$text = join(' ', $parts);
			if ($name) {
				switch ($name) {
				default: //create tag
					$name = '@'.$name;
					$tags[] = array(
						'type' => $name,
						'text' => $text,
						//'data' => $data
					);
				}
			}
		}
		return $tags;
	}

}