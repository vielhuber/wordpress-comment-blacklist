<?php
class Test extends \PHPUnit\Framework\TestCase
{  
  	protected $blacklist = '';

    protected function setUp(): void
    {
        $filename = __DIR__.'/../blacklist.txt';
        if(file_exists($filename)) {
            $this->blacklist = file_get_contents($filename);
        }
    }
  
    public function testFalsePositives()
    {
        $this->runTestInFolder('false_positives');
    }

    public function testFalseNegatives() {
        $this->runTestInFolder('false_negatives');
    }

    private function runTestInFolder($folder) {
        $files = $this->getFilesInFolder($folder);
        foreach($files as $files__value) {
            $file_content = file_get_contents(__DIR__ . '/' . $folder . '/' . $files__value);
            foreach(explode("\n", $file_content) as $file_content__value) {
                if( $file_content__value == '' ) { continue; }
                $match = null;
                foreach(explode("\n", $this->blacklist) as $blacklist__value) {
                    if( $blacklist__value == '' ) { continue; }
                    $pattern = sprintf('#%s#i', preg_quote($blacklist__value, '#'));
                    if( preg_match($pattern, $file_content__value) ) { 
                        $match = ['blacklist' => $blacklist__value, 'string' => $file_content__value];
                        break;
                    }
                }
                if( $folder === 'false_positives' && $match !== null ) {
                    $this->assertEquals('"'.$match['string'].'" => "'.$match['blacklist']. '"', 'FALSELY DETECTED AS SPAM');
                }
                else if( $folder === 'false_negatives' && $match === null ) {
                    $this->assertEquals($file_content__value, 'FALSELY NOT DETECTED AS SPAM');
                }
                else {
                    $this->assertTrue(true);
                }
            }
        }   
    }

    private function getFilesInFolder($folder) {
        if( !is_string($folder) || $folder == '' || !is_dir(__DIR__ . '/' . $folder) ) { return []; }
        return array_diff(scandir(__DIR__ . '/' . $folder), ['.', '..']);
    }
}