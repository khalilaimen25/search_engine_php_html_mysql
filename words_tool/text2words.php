<?php

class Text2Words {

    public function get_words($text, $separators, $stopwords, $co){

        $words = $this -> text_to_words($text, $separators);

        $key_words = $this -> delete_stop_words($words, $stopwords);

        $words_occ = array_count_values($key_words);

        if($co == 1){
            return $words_occ;
        }else{
            $elmnts = array();
            foreach($words_occ as $word => $value){
                $elmnts[$word] = $value * $co;   
            }    
            return $elmnts;
        }
    }

    private function text_to_words($text, $separators)
    {
        $words[] = $tok = strtok($text, $separators);
        while ($tok == true) {
            $words[] = $tok = strtok($separators);
        }
        return $words;
    }

    private function delete_stop_words($words, $stopwords)
    {
        $key_words = [];
        foreach ($words as $word) {
            $word = trim($word, " ");//delete white spaces
            if (!in_array($word, $stopwords) && strlen($word)>2) {
                array_push($key_words, $word);
            }
        }
        return $key_words;
    }
}
