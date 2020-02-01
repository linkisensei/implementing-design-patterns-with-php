<?php
/*

 * Define a family of algorithms, encapsulate each one, and make them interchangeable. Strategy lets the algorithm vary independently from the clients that use it.
 
 * Capture the abstraction in an interface, bury implementation details in derived classes

 */


/* Main Class  */
class SimpleApiConsumer {
    protected $parser;
    protected $printer;
    protected $url;

    /* function __construct(ParserStrategy $parser, PrinterStrategy $printer, string $url) { */ //Why not, PHP?
    function __construct($parser, $printer, $url) {
        $this->parser = $parser;
        $this->printer = $printer;
        $this->url = $url;
    }

    public function execute() {
        try {
            $response = file_get_contents($this->url);

            $parsed = $this->parser->parse($response);

            if($parsed !== false) {
                $this->printer->print($parsed);
            }

        } catch (\Throwable $th) {
            print_r($th);
        }
    }

    /* public function changeStrategies(ParserStrategy $new_parser, PrinterStrategy $new_printer) { */ //Why not, PHP?
    public function changeStrategies($new_parser, $new_printer) {
        $this->parser = $new_parser;
        $this->printer = $new_printer;
    }
}


/** Strategies */
interface ParserStrategy {
    public function parse($raw, $to_associative_array);
}

class JsonParserStrategy implements ParserStrategy {
    public function parse($raw, $to_associative_array = false) {

        if(!mb_detect_encoding($raw, 'UTF-8', true) ) {
            $raw = utf8_encode($raw);
        }
        $parsed = json_decode($raw, $to_associative_array);
        if (!!$parsed) {
            return $parsed;
        } else {
            return false;
        }
    }
}

class XmlParserStrategy implements ParserStrategy {
    public function parse($raw, $to_associative_array = false) {
        
        return simplexml_load_string($raw);  
    }
}


interface PrinterStrategy {
    public function  print($input);
}

class ScreenPrinterStrategy {
    public function  print($input) {
        print_r($input);
    }
}

class FilePrinterStrategy {
    private $_path;

    function __construct($path) {
        $this->_path = $path;
    }

    public function  print($input) {
        $output = print_r($input, true);
        file_put_contents($this->_path, $output);
    }
}



/* Testind some objects */

// A consumer that gets a json and displays it on the screen:

$json_to_screen = new SimpleApiConsumer(
                                new JsonParserStrategy(),
                                new ScreenPrinterStrategy(),
                                'https://trpg.ga/api/bestiario/index.php'
                            );

$json_to_screen->execute();

// A consumer that gets a json and dumps it to a file:

$json_to_file = new SimpleApiConsumer(
                                new JsonParserStrategy(),
                                new FilePrinterStrategy('./json_to_file.log'),
                                'https://trpg.ga/api/bestiario/index.php'
                            );

$json_to_file->execute();


// A consumer that gets a XML and dumps it to a file:

$json_to_file = new SimpleApiConsumer(
                                new XmlParserStrategy(),
                                new FilePrinterStrategy('./xml_to_file.log'),
                                'http://www.deviante.com.br/podcasts/sociedade-brasileira-de-nefrologia/feed/'
                            );

$json_to_file->execute();