<?php
include 'db_tool/db_connect.php';
// require_once('words_tool/stop_words_fr.php');
require_once('words_tool/text2words.php');
include 'words_tool/get_stopwords_separators.php';
include 'simplehtmldom/simple_html_dom.php';
include ('pdfparser/vendor/autoload.php') ;

//read files from root directory
$files = getDirContents('docs');

//indexation of files into BD
foreach ($files as $file) {
    //check if file already exist
    $sql = "SELECT * FROM sources WHERE source = '$file' ";
    $result = mysqli_query($conn, $sql);
    if (mysqli_num_rows($result) > 0) {
        
        //echo $file." already exist";
    } else {
        
        $file_parts = pathinfo($file);
       
        switch($file_parts['extension']){
            case "txt":
                $words = new Text2Words();
                $text = strtolower(file_get_contents($file));
                $nmbr_words = str_word_count($text, 0);
                $words = $words->get_words($text, $separators, $stopwords, 1);
                $title = substr($text,0,65)."...";
                $title = trim(preg_replace('/\s\s+/', ' ', $title));
                $description = substr($text,0,170);
                $description = trim(preg_replace('/\s\s+/', ' ', $description));
                break;
            case "html":
            case "htm":
                $words = new Text2Words();
                $html = file_get_html($file);
                $body = strtolower($html->find('body', 0)->innertext);
                $body = preg_replace('#<header(.*?)>(.*?)</header>#is', '', $body);
                $body = preg_replace('#<script(.*?)>(.*?)</script>#is', '', $body);
                $body = preg_replace('#<style(.*?)>(.*?)</style>#is', '', $body);
                $body = strip_tags($body);
                $title = strtolower($html->find('title', 0)->innertext); 
                $meta_description = strtolower($html->find("meta[name=description]")[0]->content);    
                $meta_keywords = strtolower($html->find("meta[name=keywords]")[0]->content);  
                $nmbr_words = str_word_count($body." ".$title." ".$meta_description." ".$meta_keywords, 0);
                $description = $meta_description;
                
                $bodyf = $words->get_words($body, $separators, $stopwords, 1);
                $meta_description = $words->get_words($meta_description, $separators, $stopwords, 1.5);
                $meta_keywords = $words->get_words($meta_keywords, $separators, $stopwords, 1.5);
                $titlef = $words->get_words($title, $separators, $stopwords, 1.5);

                if($title == ""){
                    $title = substr($body,0,60)."...";
                }
                if($description == ""){
                    $description = substr($body,0,170);
                }
                
                // concatenate arrays and sum elements with same keys
                $words = array_mesh($bodyf, $titlef, $meta_description, $meta_keywords);
                // print_r($words);
                break;

            case "pdf":
                $words = new Text2Words();
                $parser = new \Smalot\PdfParser\Parser();
                $pdf = $parser->parseFile($file);
                $text = strtolower($pdf->getText());
                $title = substr($text,0,60)."...";
                $title = trim(preg_replace('/\s\s+/', ' ', $title));
                $description = substr($text,0,170);
                $description = trim(preg_replace('/\s\s+/', ' ', $description));
                $nmbr_words = str_word_count($text, 0);
                $words = $words->get_words($text, $separators, $stopwords, 1);
                //echo $text;
                break;        
        }

        $sql = 'INSERT INTO `sources`(`source`, `title`, `description`) VALUES ("'.$file.'","'.$title.'","'.$description.'" )';
        $conn->query($sql);
        // insert file name into sources table
        // $sql = "INSERT INTO sources (source, title, description) VALUES ('$file', '$title', '$description)";
        // $conn->query($sql);

        $trace = "=======================================================================================================================\n";
        $trace = $trace."file : ".$file."\n";
        $trace = $trace."title : ".$title."\n";
        $trace = $trace."description : ".$description."\n";
        $trace = $trace."number of words : ".$nmbr_words." (100%)\n";
        $x = count($words) * 100 / $nmbr_words;
        $trace = $trace."number of key words : ".count($words)." (".round($x, 2)."%)\n";

        file_put_contents("trace.txt", $trace, FILE_APPEND | LOCK_EX);

        
        

        // Write the contents back to the file
        // file_put_contents("trace.txt", $trace);

        //echo $text;
        // $text = strtolower($text);
        //echo "<br><br>";

        // $words = new Text2Words();
        // $words = $words->get_words($text, $separators, $stopwords);

        //get id file
        $sql = "SELECT * FROM sources WHERE source = '$file' ";
        $result = $conn->query($sql);
        while ($row = mysqli_fetch_assoc($result)) {
            $id_file = $row['id'];
        }

        //indexation of words into BD
        foreach($words as $word => $value){
            //check if word already exist
            $sql = "SELECT * FROM words WHERE word = '$word' ";
            $result = mysqli_query($conn, $sql);
            if (mysqli_num_rows($result) > 0) {
                
            } else {
                // insert woed into words table
                $sql = "INSERT INTO words (word) VALUES ('$word')";
                $conn->query($sql);
            }
            //get id word
            $sql = "SELECT * FROM words WHERE word = '$word' ";
            $result = $conn->query($sql);
            while ($row = mysqli_fetch_assoc($result)) {
                $id_word = $row['id'];
            }
            
            //add word, occurency and source to DB
            $sql = "SELECT * FROM words_sources WHERE id_word = '$id_word' AND id_source = '$id_file' ";
            $result = mysqli_query($conn, $sql);
            if (mysqli_num_rows($result) > 0) {
                $sql = "UPDATE words_sources SET occurency = occurency + $value WHERE id_word = $id_word";
                $conn->query($sql);

            } else {
                $sql = "INSERT INTO words_sources (id_word, occurency, id_source) VALUES ('$id_word', '$value', '$id_file')";
                $conn->query($sql);

            }
            // $sql = "INSERT INTO words_sources (id_word, occurency, id_source) VALUES ('$id_word', '$value', '$id_file')";

            
        }

    }    
}

//get files from directory and subdirectories
function getDirContents($dir, &$results = array()) {
    $files = scandir($dir);

    foreach ($files as $file) {
        if (!is_dir($dir."/".$file)) {
            $results[] = $dir."/".$file;
        } else if ($file != "." && $file != "..") {
            getDirContents($dir."/".$file, $results);
            //$results[] = $path;
        }
    }

    return $results;
}

function array_mesh() {

    $numargs = func_num_args();
    $arg_list = func_get_args();
    $out = array();
    for ($i = 0; $i < $numargs; $i++) {
        $in = $arg_list[$i]; 
        foreach($in as $key => $value) {
            if(array_key_exists($key, $out)) {
                $sum = $in[$key] + $out[$key];
                $out[$key] = $sum;
            }else{
                $out[$key] = $in[$key];
            }
        }
    }

    return $out;
}