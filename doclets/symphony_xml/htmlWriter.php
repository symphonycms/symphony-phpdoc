<?php

require_once('php-markdown-1.0.1n/markdown.php');

/*
PHPDoctor: The PHP Documentation Creator
Copyright (C) 2004 Paul James <paul@peej.co.uk>

This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation; either version 2 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
*/

/** This generates the index.html file used for presenting the frame-formated
 * "cover page" of the API documentation.
 *
 * @package PHPDoctor\Doclets\Standard
 */
class HTMLWriter
{

	/** The doclet that created this object.
	 *
	 * @var doclet
	 */
	var $_doclet;

	/** The section titles to place in the header and footer.
	 *
	 * @var str[][]
	 */
	var $_sections = NULL;

	/** The directory structure depth. Used to calculate relative paths.
	 *
	 * @var int
	 */
	var $_depth = 0;

	/** The <body> id attribute value, used for selecting style.
	 *
	 * @var str
	 */
	var $_id = 'overview';

	/** The output body.
	 *
	 * @var str
	 */
	var $_output = '';

	/** Writer constructor.
	 */
	function htmlWriter(&$doclet)
    {	
		$this->_doclet =& $doclet;
	}

	/** Build the HTML header. Includes doctype definition, <html> and <head>
	 * sections, meta data and window title.
	 *
	 * @return str
	 */
	function _htmlHeader($title)
    {
		return '';
	}
    
    /** Get the HTML DOCTYPE for this output
     *
     * @return str
     */
    function _doctype()
    {
        return '';
    }
	
	/** Build the HTML footer.
   *
   * @return str
   */
	function _htmlFooter()
    {
		return '';
	}

	/** Build the HTML shell header. Includes beginning of the <body> section,
	 * and the page header.
	 *
	 * @return str
	 */
	function _shellHeader($path)
    {	
		return '';
	}
	
	/** Build the HTML shell footer. Includes the end of the <body> section, and
	 * page footer.
	 *
	 * @return str
	 */
	function _shellFooter($path)
    {
		return '';
	}
	
	/** Build the navigation bar
	 *
	 * @return str
	 */
	function _nav($path)
    {		
		return '';
	}
	
	function _sourceLocation($doc)
	{
	    if ($this->_doclet->includeSource()) {
	        $url = strtolower(str_replace(DIRECTORY_SEPARATOR, '/', $doc->sourceFilename()));
	        return str_repeat('../', $this->_depth) . 'source/' . $url . '.html#line' . $doc->sourceLine() . '" class="location">' . $doc->location();
	    } else {
	        return $doc->location();
	    }
	}

	/** Write the HTML page to disk using the given path.
	 *
	 * @param str path The path to write the file to
	 * @param str title The title for this page
	 * @param bool shell Include the page shell in the output
	 */
	function _write($path)
    {
		$phpdoctor =& $this->_doclet->phpdoctor();
		
		// make directory separators suitable to this platform
		$path = str_replace('/', DIRECTORY_SEPARATOR, $path);
		
		// make directories if they don't exist
		$dirs = explode(DIRECTORY_SEPARATOR, $path);
		array_pop($dirs);
		$testPath = $this->_doclet->destinationPath();
		foreach ($dirs as $dir) {
			$testPath .= $dir.DIRECTORY_SEPARATOR;
			if (!is_dir($testPath)) {
                if (!@mkdir($testPath)) {
                    $phpdoctor->error(sprintf('Could not create directory "%s"', $testPath));
                    exit;
                }
            }
		}
		
		// write file
		$fp = fopen($this->_doclet->destinationPath().$path, 'w');
		if ($fp) {
			$phpdoctor->message('Writing "'.$path.'"');
			//fwrite($fp, $this->_htmlHeader($title));
			//if ($shell) fwrite($fp, $this->_shellHeader($path));
			fwrite($fp, $this->_output);
			//if ($shell) fwrite($fp, $this->_shellFooter($path));
			///fwrite($fp, $this->_htmlFooter());
			fclose($fp);
		} else {
			$phpdoctor->error('Could not write "'.$this->_doclet->destinationPath().$path.'"');
            exit;
		}
	}
	
	function __removeTextFromMarkup($text) {
		return trim(preg_replace('/<[^>]*>/', '', $text));
	}	
	
	
	function _processTagsHTML(&$tags)
    {
		$tagString = '';
		foreach ($tags as $key => $tag) {
			if ($key != '@text') {
				if (is_array($tag)) {
                    $hasText = FALSE;
                    foreach ($tag as $key => $tagFromGroup) {
                        if ($tagFromGroup->text($this->_doclet) != '') {
                            $hasText = TRUE;
                        }
                    }
                    if ($hasText) {
                        $tagString .= '<dt>'.$tag[0]->displayName().":</dt>\n";
                        foreach ($tag as $tagFromGroup) {
                            $tagString .= '<dd>'.$tagFromGroup->text($this->_doclet)."</dd>\n";
                        }
                    }
				} else {
					$text = $tag->text($this->_doclet);
					if ($text != '') {
						$tagString .= '<dt>'.$tag->displayName().":</dt>\n";
						$tagString .= '<dd>'.$text."</dd>\n";
					} elseif ($tag->displayEmpty()) {
						$tagString .= '<dt>'.$tag->displayName().".</dt>\n";
					}
				}
			}
		}
        if ($tagString) {
            return "<dl>\n" . $tagString . "</dl>\n";
        }
	}
	
	/** Format tags for output.
	 *
	 * @param Tag[] tags
	 * @return str The string representation of the elements doc tags
	 */
	function _processTags(&$tags, $doc=NULL, &$dom_wrapper)
    {
	
		if(is_null($doc)) return '';
	
		$tagString = '';
		
		$found_tags = array();
		
		foreach ($tags as $key => $tag) {
			if ($key != '@text') {
				
				if (is_array($tag)) {
                    $hasText = FALSE;
                    foreach ($tag as $key => $tagFromGroup) {
                        if ($tagFromGroup->text($this->_doclet) != '') {
                            $hasText = TRUE;
                        }
                    }
                    if ($hasText) {
						foreach ($tag as $tagFromGroup) {							
							$found_tags[] = array(
								'name' => $tag[0]->displayName(),
								'text' => $tagFromGroup->text($this->_doclet),
								//'type' => $tag->typeName()
							);
                        }
                    }

				} else {
					
					$text = $tag->text($this->_doclet);
					if ($text != '') {
						
						$found_tags[] = array(
							'name' => $tag->displayName(),
							'text' => $tag->text($this->_doclet)
						);
						
					} elseif ($tag->displayEmpty()) {
						
						$found_tags[] = array(
							'name' => $tag->displayName()
						);
						
					}

				}
			}
		}
		
		$dom_tags = $doc->createElement('tags');
		
		foreach($found_tags as $tag) {
			
			$text = $tag['text'];
			
			// is a SeeAlso link, use text and not the lowercased URL
			if(is_array($text)) {
				$text = $tag['text']['text'];
			}
			
			// remove any markup so we can parse out parameter name from the description
			// phpDoctor provides in the form: "name - lorem ipsum dolor..."
			$text_unformatted = $this->__removeTextFromMarkup($text);
			$text_split = explode(' - ', $text_unformatted);
			
			if (count($text_split) > 1) {
				// get the tag name (first in split description)
				$tag_name = $text_split[0];
				// rebuild the rest of the description
				array_shift($text_split);
				$description = join(' - ', $text_split);
			} else {
				$tag_name = NULL;
				$description = $text;
			}
			
			// these types should have their descriptions converted using Markdown
			$use_markdown_description = in_array($tag['name'], array(
				'Parameters', 'Deprecated', 'Returns'
			));
			
			// don't use Deprecated/Returns if there's no type
			if(in_array($tag['name'], array('Deprecated', 'Returns')) && empty($tag_name)) {
				//continue;
			}
			
			$dom_tag = $doc->createElement('tag', ($use_markdown_description ? NULL : $description));
			$dom_tag->setAttribute('group', $tag['name']);
			
			// add a name if it exists ("See" tags don't have a name)
			if(!empty($tag_name)) $dom_tag->setAttribute('name', $tag_name);
			
			// parse paths such as "toolkit.EntryManager#setFetchSortingField()" into clean attributes
			// method parameters do not have these, so they are not found by this method's regex
			$this->parsePackageAndClassFromHyperlink($text, $dom_tag);
			
			if ($use_markdown_description) {
				
				// re-split the description to see if a parameter name exists
				$text_split = explode(' - ', $text);
				
				if (count($text_split) == 2) {
					$this->appendDescription($text_split[1], $doc, &$dom_tag);
				} else {
					$this->appendDescription($text, $doc, &$dom_tag);
				}
			}
			
			$dom_tags->appendChild($dom_tag);
			
		}
		
		// only add tags if they were found
		if (count($found_tags) > 0) $dom_wrapper->appendChild($dom_tags);
		
	}
	
	function parsePackageAndClassFromHyperlink($url, &$dom_wrapper) {

		if (preg_match("/^http/", $url)) return;

		$url_matches = array();

		$packageRegex = '[a-zA-Z0-9_\x7f-\xff .\\\\-]+';
		$labelRegex = 'package-functions|package-globals|[a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*';
        $regex = '/^\\\\?(?:('.$packageRegex.')[.\\\\])?(?:('.$labelRegex.')(?:#|::))?\$?('.$labelRegex.')(?:\(\))?$/';

		if (preg_match($regex, $url, $url_matches)) {
			
            $packageName = $url_matches[1];
            $className = $url_matches[2];
            $elementName = $url_matches[3];

			$dom_wrapper->setAttribute('package', $packageName);
			
			if ($className == '' || empty($className) || is_null($className)) {
				$className = (string)$elementName;
				$elementName = '';
			}
			
			$dom_wrapper->setAttribute('class', $className);
			
			if($elementName != '') $dom_wrapper->setAttribute('docblock', $elementName);
			
			$dom_wrapper->setAttribute('path', $url);
			
			return;
			
		}
		
	}
	
	function getSignature($element, $doc, &$dom_wrapper)
    {
		$signature = '';
		$myPackage =& $element->containingPackage();
		
		$parameters = $element->parameters();
		//return;
		if(!$parameters) return;
		
		foreach($parameters as $param) {
			
			$type =& $param->type();
			$classDoc =& $type->asClassDoc();
			
			$dom_argument = $doc->createElement('parameter');
			$dom_argument->setAttribute('name', $param->name());
			$dom_argument->setAttribute('type', $type->typeName());
			
			if ($classDoc) {
				$packageDoc =& $classDoc->containingPackage();
				
				$dom_argument->setAttribute('package', $classDoc->packageName());
				$dom_argument->setAttribute('class', $classDoc->name());
				
				//$signature .= '<a href="'.str_repeat('../', $myPackage->depth() + 1).$classDoc->asPath().'">'.$classDoc->name().'</a> '.$param->name().', ';
			} else {
				//$signature .= $type->typeName().' '.$param->name().', ';
			}
			
			$dom_wrapper->appendChild($dom_argument);
			
		}
		
		//return $parameters;
		
	}
	
	
	/** Convert inline tags into a string for outputting.
	 *
	 * @param Tag tag The text tag to process
	 * @param bool first Process first line of tag only
	 * @return str The string representation of the elements doc tags
	 */
	function _processInlineTags(&$tag, $first = FALSE)
    {
		if ($tag) {
			$description = '';
			if ($first) {
				$tags =& $tag->firstSentenceTags($this->_doclet);
			} else {
				$tags =& $tag->inlineTags($this->_doclet);
			}
            if ($tags) {
				foreach ($tags as $aTag) {
					if ($aTag) {
						$tagText = $aTag->text($this->_doclet);
						$description .= $tagText;
					}
				}
			}
			if ($first) $description = strip_tags($description);
			
			return $description;
			
			//return '<p>' . preg_replace("/\n{2,}/", "</p>\n<p>", $description) . '</p>';
		}
		return NULL;
	}
    
    /** Strip block level HTML tags from a string.
     *
     * @param str string
     * @return str
     */
    function _stripBlockTags($string)
    {
        return strip_tags($string, '<a><b><strong><i><em><code><q><acronym><abbr><ins><del><kbd><samp><sub><sup><tt><var><big><small>');
    }

	function appendDescription($description, $doc, &$dom_wrapper) {
		
		if ($description && !empty($description)) {
			
			//$markdown = $description;
			$markdown = Markdown($description);
			
			$dom_description = $doc->createElement('description');
			//$dom_description->appendChild($doc->createCDATASection($markdown));
			
			$dom_markdown = new DomDocument();
			@$dom_markdown->loadHTML($markdown);
			
			$xpath = new DOMXPath($dom_markdown);
			foreach($xpath->query('/html/body/*') as $body) {
				$dom_description->appendChild(
					$doc->importNode($body, TRUE)
				);
			}
			
			$dom_wrapper->appendChild($dom_description);
		}
		
	}


	function buildPath($object, $doc, &$dom_wrapper) {
		
		$dom_path = $doc->createElement('hyperlink');
		
		if ($object->isClass() || $object->isInterface() || $object->isException()) {
			
			$dom_path->setAttribute('type', 'class');
			$dom_path->setAttribute('package', $object->_package);
			$dom_path->setAttribute('class', $object->_name);
			$dom_wrapper->appendChild($dom_path);
			
		}
		
		elseif ($object->isField()) {
			
			$class =& $object->containingClass();
			
			$dom_path->setAttribute('type', 'field');
			$dom_path->setAttribute('package', $object->_package);
			
			if ($class) {
				// #name to class page
				$dom_path->setAttribute('class', $object->_name);
			}
			else {
				// #name to package list of globals
				//return strtolower(str_replace('.', '/', str_replace('\\', '/', $object->_package)).'/package-globals.html#').$object->_name;
			}
			
			$dom_wrapper->appendChild($dom_path);
			
		}
		
		elseif ($object->isConstructor() || $object->isMethod()) {
			
			$class =& $object->containingClass();
			
			$dom_path->setAttribute('type', 'method');
			$dom_path->setAttribute('package', $object->_package);
			
			if ($class) {
				$dom_path->setAttribute('class', $object->_name);
			} else {
				// #name to package functions list
			}
			
			$dom_wrapper->appendChild($dom_path);
			
		}
		
		elseif ($object->isGlobal()) {
			
			$dom_path->setAttribute('type', 'global');
			$dom_path->setAttribute('package', $object->_package);
			$dom_wrapper->appendChild($dom_path);
			
		}
		
		elseif ($object->isFunction()) {
			
			$dom_path->setAttribute('type', 'function');
			$dom_path->setAttribute('package', $object->_package);
			$dom_wrapper->appendChild($dom_path);
			
		}
		
	}
	

}

?>
