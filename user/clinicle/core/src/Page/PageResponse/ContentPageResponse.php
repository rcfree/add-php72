<?php
/**
 * Created by PhpStorm.
 * User: rob
 * Date: 25/01/17
 * Time: 10:16
 */

namespace ClinicLE\Page\PageResponse;

use Symfony\Component\HttpFoundation\Response;

class ContentPageResponse extends Response
{
    private $originalContent;

    /**
     * @return mixed|string
     */
    public function getOriginalContent()
    {
        return $this->originalContent;
    }
    function __construct($content,$response_code=200) {
        parent::__construct($content->output(),$response_code);
        $this->copyHeaders($content);
        $this->headers->set("Content-type",$content->mimeType());
        $this->originalContent = $content;

    }
    private function copyHeaders($content) {
        if(!$content->headers()) {
            return;
        }
        foreach($content->headers() as $header) {
            list($headKey,$headVal)=explode(":",$header);

            $this->headers->set($headKey,$headVal);
        }
    }
}