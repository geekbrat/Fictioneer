<?php
if(class_exists('Relationship') != true)
{
        class Relationship {
                public $id = null;
                public $type = null;
                public $target = null;

                public function __toString() {
                        return "Relationship: Id: " . $this->id . "; Type: " . $this->type . "; Target: " . $this->target;
                }
        }
}

function doc2html($webfile,$o_type='html') {
    error_reporting(E_ALL | E_STRICT);
    ini_set('error_reporting', E_ALL | E_STRICT);
    ini_set('display_errors', 1);

    include_once 'HtmlTagStack.php';
    require_once './htmlpurifier/library/HTMLPurifier.auto.php';

    $config = HTMLPurifier_Config::createDefault();
    $purifier = new HTMLPurifier($config);

    $path = $webfile;

/*
 * Initial code from : http://www.jackreichert.com/2012/11/09/how-to-convert-docx-to-html/
 */

    $path = "example/Mystery_Stories_for_Girls.docx";

    $documentFile = "";
    $relsFile = "";
    $relations = array();
    $images = array();

    // set up variables for formatting
    $chapter = '';
    $chapterId = '';
    $chapters = array();
    $chapterNames = array();
    $text = '';
    $formatting['bold'] = 'closed';
    $formatting['italic'] = 'closed';
    $formatting['underline'] = 'closed';
    $formatting['header'] = 0;
    $tocRef = null;
    $tocRefWrCount = 0;
    $toc = array();


    $zip = new ZipArchive;
    $res = $zip->open($path);
    if ($res === TRUE) {
        $documentFile = $zip->getFromName("word/document.xml");
        $relsFile = $zip->getFromName("word/_rels/document.xml.rels");
    } else {
        echo "<p>File $path could not be oppened</p>\n";
        exit();
    }


    $relsReader = new XMLReader;
    $relsReader->XML($relsFile);
    while ($relsReader->read()) {
        if ($relsReader->nodeType == XMLREADER::ELEMENT && $relsReader->name === 'Relationship') {
            $node = trim($relsReader->readOuterXml());
            $rel = new Relationship();
            $rel->id = $relsReader->getAttribute('Id');
            $rel->type = $relsReader->getAttribute('Type');
            $rel->target = $relsReader->getAttribute('Target');

            $relations[$rel->id] = $rel;
        }
    }

    // set location of docx text content file
    $reader = new XMLReader;
    $reader->XML($documentFile);

    // loop through docx xml dom
    while ($reader->read()) {
        // look for new paragraphs
        if ($reader->nodeType == XMLREADER::ELEMENT && $reader->name === 'w:p') {
            $elementId = null;
            $title = "";

            // set up new instance of XMLReader for parsing paragraph independantly
            $paragraph = new XMLReader;
            $p = $reader->readOuterXML();
            $paragraph->xml($p);

            if (strstr($p, 'w:bookmarkStart ')) {
                // <w:bookmarkStart w:id="4" w:name="_Toc362253443"/>
                preg_match('/w:bookmarkStart.+?w:name="([^"]+)"/',$p,$matches);
                if (sizeof($matches) == 2) {
                    $wName = $matches[1];
                    $text .= $chapter;

                    if (strlen($chapter) > 0) {
                        $c = $purifier->purify(tag_sanitizer($chapter));
                        $chapters[$chapterId] = $content_start . $c . $content_end;
                    }

                    $elementId = $wName;
                    $chapterId = $wName;
                    $chapter = "\n<!-- EPUB::BOOKMARK='" . $wName . "' -->\n";

                }
            }

            // search for heading
            preg_match('/<w:pStyle w:val="(Heading.*?[1-6])"/',$p,$matches);
            if (sizeof($matches) >= 2) {
                switch($matches[1]){
                    case 'Heading1':
                        $formatting['header'] = 1;
                        break;
                    case 'Heading2':
                        $formatting['header'] = 2;
                        break;
                    case 'Heading3':
                        $formatting['header'] = 3;
                        break;
                    case 'Heading4':
                        $formatting['header'] = 4;
                        break;
                    case 'Heading5':
                        $formatting['header'] = 5;
                        break;
                    case 'Heading6':
                        $formatting['header'] = 6;
                        break;
                    default:
                        $formatting['header'] = 0;
                        break;
                }
            } else {
                $formatting['header'] = 0;
            }

            // open h-tag or paragraph
            $chapter .= ($formatting['header'] > 0) ? '<h'.$formatting['header'] : '<p';
            if (isset($elementId)) {
                $chapter .= ' id="' . $elementId . '"';
            }
            $chapter .= '>';

            // loop through paragraph dom
            while ($paragraph->read()) {

                // look for elements
                if ($paragraph->nodeType != XMLREADER::ELEMENT) {
                    continue;
                }
                $paragraphName = $paragraph->name;
                if($paragraphName === 'w:hyperlink') {
                    $node = trim($paragraph->readOuterXml());

                    preg_match('/.*w:hyperlink.+w:anchor="([^"]+)"/',$node,$matches);
                    if (sizeof($matches) == 2) {

                        $tocRefWrCount = substr_count($node, '<w:r ');

                        $tocRef = $matches[1];
                        $chapter .= '<a href="#' . $tocRef . '">';
                        $toc[$tocRef] = $tocRef;
                    }
                } else if ($paragraphName === 'pic:pic') {
                    $picName = null;
                    $picId = null;

                    $picNode = new XMLReader;
                    $p = $paragraph->readOuterXML();
                    $picNode->xml($p);
                    while ($picNode->read()) {
                        if ($picNode->nodeType != XMLREADER::ELEMENT) {
                            continue;
                        }
                        $nodeName = $picNode->name;

                        if ($nodeName === 'pic:cNvPr') {
                            $picName = $picNode->getAttribute("name");
                        } else if ($nodeName === 'a:blip') {
                            $picId = $picNode->getAttribute("r:embed");
                        }
                    }

                    if (isset($picId) && array_key_exists($picId, $relations)) {
                        $relation = $relations[$picId];

                        $image = $zip->getFromName("word/" . $relation->target);

                        if (isset($picName)) {
                            $chapter .= '<img src="images/' . $picName . '"  id ="' . $picId . '" alt="Image"/>';
                            $images[$picId] = 'images/' . $picName;
                        } else {
                            $chapter .= '<img src="' . $relation->target . '"  id ="' . $picId . '" alt="Image"/>';
                            $images[$picId] = $relation->target;
                        }

                    }
                } else if ($paragraphName === 'w:r') {
                    $tocRefWrCount--;
                    $node = trim($paragraph->readInnerXML());

                    // add <br> tags
                    if (strstr($node,'<w:br ')) {
                        $chapter .= "<br />\n";
                    }

                    if (strstr($node,'<w:webHidden')) {
                        continue;
                    }
                    if (strstr($node,'<w:instrText ')) {
                        continue;
                    }

                    // look for formatting tags
                    $formatting['bold'] = (strstr($node,'<w:b/>')) ? (($formatting['bold'] == 'closed') ? 'open' : $formatting['bold']) : (($formatting['bold'] == 'opened') ? 'close' : $formatting['bold']);
                    $formatting['italic'] = (strstr($node,'<w:i/>')) ? (($formatting['italic'] == 'closed') ? 'open' : $formatting['italic']) : (($formatting['italic'] == 'opened') ? 'close' : $formatting['italic']);
                    $formatting['underline'] = (strstr($node,'<w:u ')) ? (($formatting['underline'] == 'closed') ? 'open' : $formatting['underline']) : (($formatting['underline'] == 'opened') ? 'close' : $formatting['underline']);

                    //echo htmlentities(iconv('UTF-8', 'ASCII//TRANSLIT',$paragraph->expand()->textContent))."\n";

                    // build text string of doc
                    $chapter .=    (($formatting['bold'] == 'open') ? '<strong>' : '').
                                (($formatting['italic'] == 'open') ? '<em>' : '').
                                (($formatting['underline'] == 'open') ? '<span class="underline">' : '').
                                //htmlentities(iconv('UTF-8', 'ASCII//TRANSLIT',$paragraph->expand()->textContent)).
                                htmlentities($paragraph->expand()->textContent, ENT_COMPAT, 'UTF-8').
                                (($formatting['underline'] == 'close') ? '</span>' : '').
                                (($formatting['italic'] == 'close') ? '</em>' : '').
                                (($formatting['bold'] == 'close') ? '</strong>' : '');

                    if (isset($elementId)) {
                        //$title .= iconv('UTF-8', 'ASCII//TRANSLIT',$paragraph->expand()->textContent);
                        $title .= $paragraph->expand()->textContent;
                    }

                    if (isset($tocRef) && $tocRefWrCount <= 0) {
                        $chapter .= '</a>';
                        unset($tocRef);
                    }

                    // reset formatting variables
                    foreach ($formatting as $key=>$format){
                        if ($format == 'open') {
                            $formatting[$key] = 'opened';
                        }
                        if ($format == 'close') {
                            $formatting[$key] = 'closed';
                        }
                    }
                }
            }
            if (isset($tocRef)) {
                $chapter .= '</a>';
                unset($tocRef);
            }
            $chapter .= ($formatting['header'] > 0) ? "</h" . $formatting['header'] . ">\n" : "</p>\n";
            if (isset($title)) {
                $chapterNames[$elementId] = $title;
                unset($title);
            }

        }
    }
    $reader->close();
    $zip->close();

    $text .= $chapter;

    if (strlen($chapter) > 0) {
        $c = $purifier->purify(tag_sanitizer($chapter));
        $chapters[$chapterId] = $content_start . $c . $content_end;
    }

    // suppress warnings. loadHTML does not require valid HTML but still warns against it...
    // fix invalid html
    $doc = new DOMDocument();
    $doc->encoding = 'UTF-8';
    @$doc->loadHTML($content_start . $text . $content_end);
    $goodHTML = simplexml_import_dom($doc)->asXML();

    return $goodHTML;
}

