<?php
/*--------------------------------------------------------------*\
    Description:    HTML template class based on bTemplate.
    Author:         Takayuki Miyauchi (miya@theta.ne.jp)
    License:        MIT License
    Version:        0.1
\*--------------------------------------------------------------*/
if (!class_exists('tinyTemplate')) {
class tinyTemplate {
    // Configuration variables
    private $base_path = '';
    private $reset_vars = TRUE;

    // Default Modifier
    public $default_modifier = null;

    // Delimeters for regular tags
    private $ldelim = '{';
    private $rdelim = '}';

    // Delimeters for beginnings of loops
    private $BAldelim = '{';
    private $BArdelim = '}';

    // Delimeters for ends of loops
    private $EAldelim = '{/';
    private $EArdelim = '}';

    // Internal privateiables
    private $scalars = array();
    private $arrays  = array();
    private $carrays = array();
    private $ifs     = array();


//
// Simply sets the base path (if you don't set the default).
//
    function __construct($base_path = NULL, $reset_vars = TRUE)
    {
        if($base_path) $this->base_path = $base_path;
        $this->reset_vars = $reset_vars;
        $this->default_modifier = array($this, 'modifier');
    }


//
// Sets all types of variables (scalar, loop, hash).
//
    public function set($tag, $var, $modifier = true)
    {
        if(is_array($var)) {
            if ($modifier) {
                array_walk_recursive($var, $this->default_modifier);
            }
            $this->arrays[$tag] = $var;
            $result = $var ? TRUE : FALSE;
            $this->ifs[] = $tag;
            $this->scalars[$tag] = $result;
        } else {
            if ($modifier) {
                call_user_func_array($this->default_modifier, array(&$var));
            }
            $this->scalars[$tag] = $var;
            $this->ifs[] = $tag;
        }
    }


//
//  Returns the parsed contents of the specified template.
//
    public function fetch($file_name)
    {
        $file = $this->base_path . $file_name;

        $fp = fopen($file, 'rb');
        if(!$fp) return FALSE;
        $contents = fread($fp, filesize($file));
        fclose($fp);

        return $this->parse($contents);
    }


//
// Parses all variables into the template.
//
    public function parse($contents)
    {
        $this->set('_SERVER', $_SERVER);
        $this->set('_POST', $_POST);
        $this->set('_GET', $_GET);
        $this->set('_COOKIE', $_COOKIE);

        // Process the ifs
        if(!empty($this->ifs)) {
            foreach($this->ifs as $value) {
                $contents = $this->parse_if($value, $contents);
            }
        }

        // Process the scalars
        foreach($this->scalars as $key => $value) {
            $contents = str_replace($this->get_tag($key), $value, $contents);
        }

        // Process the arrays
        foreach($this->arrays as $key => $array) {
            $contents = $this->parse_loop($key, $array, $contents);
        }

        // Process the carrays
        foreach($this->carrays as $key => $array) {
            $contents = $this->parse_cloop($key, $array, $contents);
        }

        // Reset the arrays
        if($this->reset_vars) $this->reset_vars(FALSE, TRUE, TRUE, FALSE);

        // Return the contents
        return $contents;
    }


//
// Set filter function for template output
//
    public function setModifier($modifier)
    {
        if (is_array($modifier)) {
            if (method_exists($modifier[0], $modifier[1])) {
                $this->default_modifier = $modifier;
            }
        } elseif(function_exists($modifier)) {
            $this->default_modifier = $modifier;
        }
    }


    private function set_cloop($tag, $array, $cases)
    {
        $this->carrays[$tag] = array(
            'array' => $array,
            'cases' => $cases);
    }


    private function reset_vars($scalars, $arrays, $carrays, $ifs)
    {
        if($scalars) $this->scalars = array();
        if($arrays)  $this->arrays  = array();
        if($carrays) $this->carrays = array();
        if($ifs)     $this->ifs     = array();
    }


    private function get_tags($tag, $directive)
    {
        $tags['b'] = $this->BAldelim . $directive . $tag . $this->BArdelim;
        $tags['e'] = $this->EAldelim . $directive . $tag . $this->EArdelim;
        return $tags;
    }


    private function get_tag($tag) {
        return $this->ldelim . '$' . $tag . $this->rdelim;
    }


    private function get_statement($t, &$contents) {
        // Locate the statement
        $tag_length = strlen($t['b']);
        $fpos = strpos($contents, $t['b']) + $tag_length;
        $lpos = strpos($contents, $t['e']);
        $length = $lpos - $fpos;

        // Extract & return the statement
        return substr($contents, $fpos, $length);
    }


    private function parse_if($tag, $contents)
    {
        // Get the tags
        $t = $this->get_tags($tag, 'if:$');
        
        // Get the entire statement
        $entire_statement = $this->get_statement($t, $contents);
        
        // Get the else tag
        $tags['b'] = NULL;
        $tags['e'] = $this->BAldelim . 'else:$' . $tag . $this->BArdelim;        
        
        // See if there's an else statement
        if(($else = strpos($entire_statement, $tags['e']))) {        
            // Get the if statement
            $if = $this->get_statement($tags, $entire_statement);
        
            // Get the else statement
            $else = substr($entire_statement, $else + strlen($tags['e']));
        }
        else {
            $else = NULL;
            $if = $entire_statement;
        }
        
        // Process the if statement
        $this->scalars[$tag] ? $replace = $if : $replace = $else;

        // Parse & return the template
        return str_replace(
            $t['b'] . $entire_statement . $t['e'],
            $replace,
            $contents
        );
    }


    private function parse_loop($tag, $array, $contents)
    {
        // Get the tags & loop
        $t = $this->get_tags($tag, 'loop:$');
        $loop = $this->get_statement($t, $contents);
        $parsed = NULL;

        // Process the loop
        foreach($array as $key => $value) {
            if(is_numeric($key) && is_array($value)) {
                $i = $loop;
                foreach($value as $key2 => $value2) {
                    if(!is_array($value2)) {
                        // Replace associative array tags
                        $i = str_replace($this->get_tag($tag . '[].' . $key2), $value2, $i);
                    }
                    else {
                        // Check to see if it's a nested loop
                        $i = $this->parse_loop($tag . '[].' . $key2, $value2, $i);
                    }
                }
            }
            elseif(is_string($key) && !is_array($value)) {
                $contents = str_replace($this->get_tag($tag . '.' . $key), $value, $contents);
            }
            elseif(!is_array($value)) {
                $i = str_replace($this->get_tag($tag . '[]'), $value, $loop);
            }

            // Add the parsed iteration
            if(isset($i)) $parsed .= rtrim($i);
        }

        // Parse & return the final loop
        return str_replace($t['b'] . $loop . $t['e'], $parsed, $contents);
    }


    private function parse_cloop($tag, $array, $contents)
    {
        // Get the tags & loop
        $t = $this->get_tags($tag, 'cloop:');
        $loop = $this->get_statement($t, $contents);

        // Set up the cases
        $array['cases'][] = 'default';
        $case_content = array();
        $parsed = NULL;

        // Get the case strings
        foreach($array['cases'] as $case) {
            $ctags[$case] = $this->get_tags($case, 'case:');
            $case_content[$case] = $this->get_statement($ctags[$case], $loop);
        }

        // Process the loop
        foreach($array['array'] as $key => $value) {
            if(is_numeric($key) && is_array($value)) {
                // Set up the cases
                if(isset($value['case'])) $current_case = $value['case'];
                else $current_case = 'default';
                unset($value['case']);
                $i = $case_content[$current_case];

                // Loop through each value
                foreach($value as $key2 => $value2) {
                    $i = str_replace($this->get_tag($tag . '[].' . $key2), $value2, $i);
                }
            }

            // Add the parsed iteration
            $parsed .= rtrim($i);
        }

        // Parse & return the final loop
        return str_replace($t['b'] . $loop . $t['e'], $parsed, $contents);
    }


    private function modifier(&$str)
    {
        $str = htmlentities($str, ENT_QUOTES, mb_internal_encoding());
        $str = trim($str);
        return $str;
    }
}
}
?>
