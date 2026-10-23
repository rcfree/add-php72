<?php
namespace ClinicLE\System\Service\Minify;
use MatthiasMullie\Minify;

class FullJS extends Minify\JS {
	/**
     * Minify the data.
     * Perform JS optimizations.
     *
     * @param string[optional] $path Path to write the data to.
     *
     * @return string The minified data.
     */
    public function execute($path = null)
    {
        $content = '';
        // loop files
        foreach ($this->data as $source => $js) {
            /*
             * Combine js: separating the scripts by a ;
             * I'm also adding a newline: it will be eaten when whitespace is
             * stripped, but we need to make sure we're not just appending
             * a new script right after a previous script that ended with a
             * singe-line comment on the last line (in which case it would also
             * be seen as part of that comment)
             */
            $content .= $js."\n;";
        }
        return $content;
    }
   }