<?php

class GlobalWriter extends HTMLWriter {

	function globalWriter(&$doclet) {
	
		parent::HTMLWriter($doclet);

		$rootDoc =& $this->_doclet->rootDoc();
        
        $packages =& $rootDoc->packages();
        ksort($packages);

		foreach($packages as $packageName => $package) {
			
			$doc = new DomDocument();
			$doc->preserveWhiteSpace = FALSE;
			$doc->formatOutput = TRUE;
			
			$dom_globals = $doc->createElement('constants');
			$dom_globals->setAttribute('package', $package->name());
			
			$globals =& $package->globals();
				
			if ($globals) {				
                ksort($globals);
				
				foreach($globals as $global) {
					
					$dom_global = $doc->createElement('constant');
					
					$dom_modifiers = $doc->createElement('modifiers');
					foreach(explode(' ', trim($global->modifiers())) as $modifier) {
						$dom_modifiers->appendChild($doc->createElement('modifier', $modifier));
					}
					$dom_global->appendChild($dom_modifiers);
					
					$type = $global->typeAsString();
					$type = $this->__removeTextFromMarkup($type);
					
					$dom_global->setAttribute('name', $global->name());
					$dom_global->setAttribute('type', $type);
					
					$dom_signature = $doc->createElement('parameters');
					#$this->getSignature($global, $doc, $dom_signature);
					$dom_global->appendChild($dom_signature);
					
					$dom_location = $doc->createElement('location', $global->sourceFilename());
					$dom_location->setAttribute('line', $global->sourceLine());
					$dom_global->appendChild($dom_location);
					
					$textTag =& $global->tags('@text');
					$this->appendDescription($this->_processInlineTags($textTag), $doc, $dom_global);

					$dom_globals->appendChild($dom_global);
					
				}
				
			}

			$doc->appendChild($dom_globals);
			$this->_output = $doc->saveXML();
			$this->_write($package->asPath().'/package-constants.xml');
		}
	
	}

}