<?php
include 'db_tool/db_connect.php';
// require_once('words_tool/stop_words_fr.php');
require_once('words_tool/text2words.php');
include 'words_tool/get_stopwords_separators.php';
include 'simplehtmldom/simple_html_dom.php';
include ('pdfparser/vendor/autoload.php') ;

require __DIR__ . '/vendor/autoload.php';//google search API

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.3.1/css/all.css" integrity="sha384-mzrmE5qonljUremFsqc01SB46JvROS7bZs3IO2EmfFsd15uHvIt+Y8vEf7N7fWAU" crossorigin="anonymous">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-1BmE4kWBq78iYhFldvKuhfTAU6auU8tT94WrHftjDbrCEXSU1oBoqyl2QvZ6jIW3" crossorigin="anonymous">
    <link rel="stylesheet" type="text/css" href="style.css"/>

    <title>My Search Engine</title>

</head>
<body>
<?php
    $search_text = $_GET['search_text'];
?>
<div class="container"> 
  
<div>
<a href="app.php">
<img src = "search.svg" class="logo2"/>
</a>

<form action="" method="GET" class="result_form app_form">
        <input type="search" name="search_text" value="<?php echo $search_text; ?>" required>
        <!-- <i class="fa fa-search" id="search" name="search" onclick="this.closest('form').submit();return false;"></i> -->
        <button type="submit" class="btn btn-default">
        <i class="fas fa-search" id="search"></i>
        </button>

        <a class="clear" href="javascript:void(0)" id="clear-btn">Clear Search</a>
    </form>    
</div>

<?php
    // google api spell check
    $spell_correction = "";
    try{
        $query = ["q" => $search_text,"hl" => "fr"];
        $client = new GoogleSearch("de52e967ac43abc2131286a7c8172a7418330e5af3f67ae5de5f61790322643a");
        $response = $client->get_json($query);
        $search_information = $response->search_information;
    
        $spell_correction = $search_information->spelling_fix;
    
        if($spell_correction != ""){
            $search_text = $spell_correction;
        }
    }catch(Exception $e){

    }
    //echo $search_text;
    $search_text = strtolower($search_text);

    $words = new Text2Words();
    $words = $words->get_words($search_text, $separators, $stopwords, 1);

    $words_sources = [];
    $sources_list = [];
    $sources_list_details = [];
    // print_r($words);
    foreach ($words as $word => $value) {
        // echo $word . "<br>";
        
        $sql = "SELECT word, occurency, source, title, description FROM words_sources wa, words w, sources s 
                WHERE 
                wa.id_word = w.id 
                AND s.id = wa.id_source 
                AND w.word  = '$word' 
                ORDER BY occurency DESC";

        $result = mysqli_query($conn, $sql);
        if (mysqli_num_rows($result) > 0){
            //echo "found <br>";
            while ($row = mysqli_fetch_assoc($result)) {
                $source = $row['source'];
                $occurency = $row['occurency'];
                $title = $row['title'];
                $description = $row['description'];
                $my_res = [$word, $occurency, $source];
                array_push($words_sources, $my_res); //creates matrix of data [word, occurency, source]
                if(!in_array($source, $sources_list)){
                    array_push($sources_list, $source); //create sources list (array of sources)
                    array_push($sources_list_details, [$source,  $title,  $description ]);
                    //echo "word : ".$word." / source : ".$source." /occ: ".$occurency."<br>";     
                }
            }
        }
    }

    // print_r($sources_list);
    $id = count($sources_list); 
    if(count($sources_list) > 0){
        if($spell_correction != $$search_text){
        echo "<p class='spell_correct'>Did you mean <span class='spell_correct_res'>".$search_text." </span>?</p>";
        }
        echo "<h4> We found ".count($sources_list)." results.</h4>";
        // organizing the matrix by source (groupe data with the same source) 
        foreach($sources_list_details as $ii => $s){
            $group_source = [];
            
            foreach($words_sources as $i => $item){
                
                if($item[2] == $s[0]){
                    array_push($group_source, $item);
                } 
            }
               
            echo   '<div class="box"> 
                    <h4 class="title">'.$s[1].'</h4>
                    <a href="'.$s[0].'" target=\"_blank\" class="source_link"><p>'.$s[0].'</p></a>';
                    echo '<span class="result-text">'.$s[2].'...</span><br><hr>';
            
                   
                    foreach($group_source as $iii => $value){
                        // if($kw == $value[0]) break;
                    echo   '<span class="word-occ">The word <strong>'.$value[0].'</strong> appeared <strong>'.$value[1].'</strong> times .</span><br>';
                        // $kw = $value[0];
                    }

            $sql = "SELECT word, occurency FROM words w, words_sources wa, sources s 
            WHERE w.id = wa.id_word AND s.id = wa.id_source AND s.source = '$value[2]'; ";
            
            $result = mysqli_query($conn, $sql);
                    if (mysqli_num_rows($result) > 0){
                        $words_occurency = array();
            
                        while ($row = mysqli_fetch_assoc($result)) {
                            $word = $row['word'];
                            $occurency = $row['occurency'];
                            $words_occurency[$word] = $occurency; 
                        }
                    }
            draw_cloud($words_occurency, $id);                    
           
            echo '<button id="showcloud" class="btn btn-primary show_cloud" onclick="show_cloud('.$id.')"><i class="fa fa-cloud"></i></button>
                  </div>';
            $id=$id-1;      
        }
    }else
        echo "<h1> Sorry, No result was found...!</h1>";


    function draw_cloud( $words_occurency, $id){
        // randomize array
        $keys = array_keys($words_occurency);
        shuffle($keys);
        foreach($keys as $key){
            $new[$key] = $words_occurency[$key];
        }
        $words_occurency = $new;
        
        $starting_font_size = 15;
        $factor = 10;
          
        $colors = array("black","gray","maroon","red","purple","fuchsia","green",
                        "lime","olive","navy","blue","teal","aqua","darkorange","gold","olivedrab","rosybrown");
                     
        echo '<div class="cloud2-div" id='.$id.'>';
        foreach($words_occurency as $word => $value){
            if($value >= 1){

                $rand = array_rand($colors, 1);
                $color = $colors[$rand];
                $x =  round( ($value/2) * $factor);
                $font_size = $starting_font_size + $x;
                if($font_size > 150){
                    $font_size = 150;
                }
                echo '
                    <span class="cloud-tag" style="color:'.$color.'; font-size: '.$font_size.'px; ">'
                    .$word.'
                    </span>
                    '; 
            }
        }
        echo '</div>';
    }    
?>

<script>
    const clearInput = () => {
        const input = document.getElementsByTagName("input")[0];
        input.value = "";
    }
  
    const clearBtn = document.getElementById("clear-btn");
    clearBtn.addEventListener("click", clearInput); 

    function show_cloud(id) {
        // console.log("click", id)
        var x = document.getElementById(id);
        if (x.style.display === "block") {
            x.style.display = "none";
        } else {
            x.style.display = "block";
        }
    } 
</script>
</div>
<p class="myname">Copyright - HAMAMA Aimen Khalil</p>
</body>
</html>