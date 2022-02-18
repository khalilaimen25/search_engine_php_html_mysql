<?php 
    include "index.php";
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    
    <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.3.1/css/all.css" integrity="sha384-mzrmE5qonljUremFsqc01SB46JvROS7bZs3IO2EmfFsd15uHvIt+Y8vEf7N7fWAU" crossorigin="anonymous">
    <!-- CSS only -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-1BmE4kWBq78iYhFldvKuhfTAU6auU8tT94WrHftjDbrCEXSU1oBoqyl2QvZ6jIW3" crossorigin="anonymous">
    <link rel="stylesheet" type="text/css" href="style.css" />
    <title>My Search Engine</title>
      
</head>
<body>

<?php
    // $search_text = "";
    // if(isset($_GET['search'])){
    //   $search_text = $_GET['search_text'];
    // }
?>    
    
    <img src = "search.svg" class="logo"/>


    <form action="result.php" method="GET" class="app_form">

      <input type="search" name="search_text" required>
      <!-- <i class="fa fa-search" id="search" name="search" onclick="this.closest('form').submit();return false;"></i> -->
        <button type="submit" class="btn btn-default">
        <i class="fas fa-search" id="search"></i>
        </button>

      <a class="clear" href="javascript:void(0)" id="clear-btn">Clear Search</a>
    </form>
    
    <h2 class="logo_text">What are you looking for ?</h2>
    

  <?php
    // if(!empty($search_text)){

    //   $search_text = strtolower($search_text);
    //   //echo "<h1>text = ".$search_text."</h1>";

    //   $words = new Text2Words();
    //   $words = $words->get_words($search_text, $separators, $stopwords);
  
    //   foreach($words as $i => $word){
    //     echo $i."<br>";
  
    //   }
    // }


    ?>  

<script>
    const clearInput = () => {
        const input = document.getElementsByTagName("input")[0];
        input.value = "";
        
    }

    // const getsearchinput = () => {
    //     const input = document.getElementsByTagName("input")[0];
    //     console.log(input.value);
      
    // }
  
  const clearBtn = document.getElementById("clear-btn");
  clearBtn.addEventListener("click", clearInput);

  // const search = document.getElementById("search");
  // search.addEventListener("click", getsearchinput);
  
  
  
</script>    

<p class="myname">Copyright - HAMAMA Aimen Khalil</p>

</body>
</html>



