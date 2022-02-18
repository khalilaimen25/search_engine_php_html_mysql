<?php
//preparer les mots vides
$file = 'words_tool/stop_words_fr.txt';
$text = file_get_contents($file);
$stopwords[] = $tok = strtok($text, ",");
while ($tok == true) {
	$stopwords[] = $tok = strtok(",");
}

// $stopwords = new StopWords_French();
// $stopwords = $stopwords -> stopwords();

//preparer separateurs
$separators = " ,.(){}[]'_-^|&<>°`¤£µ=+:;?§!/~$%*\"\#\\«»/\r\n|\n|\r/●";

?>