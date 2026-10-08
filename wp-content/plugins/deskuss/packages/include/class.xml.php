<?php

//////////////////////////////////////////////////////////////
//===========================================================
// SOFTACULOUS PROJECT
//===========================================================
// Inspired by the DESIRE to be the BEST OF ALL
// ----------------------------------------------------------
// Started by: Pulkit and Brijesh
// ----------------------------------------------------------
// Please Read the Terms of use at http://deskuss.com
// ----------------------------------------------------------
//===========================================================
// (c)Softaculous Ltd.
//===========================================================
//////////////////////////////////////////////////////////////


class XmlDataParser {

    function __construct() {
        $this->parser = xml_parser_create('utf-8');
        xml_set_object($this->parser, $this);
        xml_set_element_handler($this->parser, "startElement", "endElement");
        xml_set_character_data_handler($this->parser, "content");
        xml_parser_set_option($this->parser, XML_OPTION_CASE_FOLDING, 0);
        xml_parser_set_option($this->parser, XML_OPTION_SKIP_WHITE, 1);
        $this->content = array();
        $this->stack = array();
    }

    function parse($stream) {
        while ($data = fread($stream, 4096)) {
            if (!xml_parse($this->parser, $data, feof($stream)))
                return false;
        }
        $this->content = $this->reduce($this->content);
        return $this->content;
    }

    function lastError() {
        return sprintf(__('XML error: %1$s at line %2$d:%3$d'),
            xml_error_string(xml_get_error_code($this->parser)),
            xml_get_current_line_number($this->parser),
            xml_get_current_column_number($this->parser));
    }
    /**
     * Remove empty content and collapse simple elements (those that only
     * have text content (not xml element content).
     */
    function reduce($start) {
        if (count($start) == 1 and isset($start[":text"])) {
            # Collapse the :text to simple value for the key
            return $start[":text"];
        }
        else if (strlen($start[":text"]) == 0) { unset($start[":text"]); }
        # Recurse
        foreach ($start as $name => &$value) {
            if (is_array($value)) $value = $this->reduce($value);
        }
        return $start;
    }

    function startElement($parser, $name, $attrs) {
        # Start a new array to hold upcoming contents. Push the current
        # content onto a stack to become the "parent" content
        array_push($this->stack, $this->content);
        $this->content = array(":text" => "");
        foreach ($attrs as $name=>$value)
            $this->content[$name] = $value;
    }

    function endElement($parser, $name) {
        # When an element is closed, associate the current content with the
        # name of the element in the parent content array.
        $prev = $this->content;
        $this->content = array_pop($this->stack);
        $i = 1;
        if (array_key_exists($name, $this->content)) {
            if(!isset($this->content[$name][0])) {
                $current = $this->content[$name];
                unset($this->content[$name]);
                $this->content[$name][0] = $current;
            }
            $this->content[$name][] = $prev;
        } else
            $this->content[$name] = $prev;
    }

    function content($parser, $data) {
        $this->content[":text"] .= trim($data);
    }
}

?>
