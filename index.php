<?php
session_start();
if (version_compare(PHP_VERSION, '5.3.7', '<')){
    exit("Sorry, Simple PHP Login does not run on a PHP version smaller than 5.3.7 !");
}else if (version_compare(PHP_VERSION, '5.5.0', '<')){
    require_once("libraries/password_compatibility_library.php");
}
require_once("config/db.php");
?>
<html>
<head>
  <link rel="stylesheet" href="http://code.jquery.com/mobile/1.4.5/jquery.mobile-1.4.5.min.css" />
  <script src="https://code.jquery.com/jquery-3.1.1.min.js" type="text/javascript"></script>
  <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css">
  <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.1.1/jquery.min.js"></script>
  <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js"></script>
  <script src="pace.js"></script>
  <link href="pace-theme-loading-bar.css" rel="stylesheet" />
</head>
<style>
body > :not(.pace),body:before,body:after {
  -webkit-transition:opacity .4s ease-in-out;
  -moz-transition:opacity .4s ease-in-out;
  -o-transition:opacity .4s ease-in-out;
  -ms-transition:opacity .4s ease-in-out;
  transition:opacity .4s ease-in-out
}

body:not(.pace-done) > :not(.pace),body:not(.pace-done):before,body:not(.pace-done):after {
  opacity:0
}
body{
  background-color:#F3EFE0;
}
</style>
<body>
<div style="width:100%;background-color:#87CEFA;">
<div style="margin-left:auto;margin-right:auto;text-align:center;">
  <img src="images/logo.png" style="width:300px;margin-top:20px;">
</div>
<div class="container" style = "width:350px;margin-top:40px;">
<form class="form-signin" method="post" action="loginProcessing.php" name="loginform">
    <h2 class="form-signin-heading">Sign in</h2>
    <label class="sr-only" for="Username">Username</label>
    <input class="form-control" style = "height:45px;" placeholder="Username" id="Username" class="login_input" type="text" name="user_name" required autofocus autocomplete="off"/>
    <br>
    <label class="sr-only" for="Password">Password</label>
    <input class="form-control" style = "height:45px;" placeholder="Password" id="Password" class="login_input" type="password" name="user_password" autocomplete="off" required autofocus autocomplete="off"/>
    <div class="checkbox">
      <label>
        <input type="checkbox" value="remember-me"> Remember me
      </label>
    </div>
    <button class="btn btn-lg btn-primary btn-block" type="submit"  name="login">Log in</button>
</form>
<a href="register.php" style="color:black;">Register new account</a>
<br>
<br>
<div id = "message" style="color:black;">
  <?php
    if(isset($_SESSION['loginErrorMsg'])){
      echo $_SESSION['loginErrorMsg'];
      unset($_SESSION['loginErrorMsg']);
    }
  ?>
</div>
</div>
</div>
<div class="row" style="width:100%;margin-top:60px;">
  <div class="col-md-6">
      <h2 class="form-signin-heading" style="text-align:center;">Current Groups</h2>
        <div class="row">
            <div class="col-lg-4 col-lg-offset-4">
                <input type="search" id="search" value="" class="form-control" style="text-align:center;"  placeholder="Search Groups">
            </div>
        </div>
        <div class="row">
            <div class="col-lg-12">
                <table class="table table-striped" id="table">
                    <thead>
                        <tr>
                            <th>Group Name</th>
                            <th>Category</th>
                            <th>Keywords</th>
                        </tr>
                    </thead>
                    <tbody>

                    </tbody>
                </table>
                <hr>
            </div>
        </div>
</div>


<div class="col-md-6">
      <h2 class="form-signin-heading" style="text-align:center;">Group events in the next 3 days</h2>
      <div class="row">
          <div class="col-lg-4 col-lg-offset-4">
            <input type="search" id="searchEvent" value="" class="form-control" style="text-align:center;" placeholder="Search Group Events">
          </div>
      </div>
      <div class="row">
          <div class="col-lg-12">
              <table class="table table-striped" id="EventTable">
                  <thead>
                      <tr>
                          <th>Title</th>
                          <th>Description</th>
                          <th>Start Time</th>
                          <th>End Time</th>
                          <th>Location Name</th>
                          <th>Zip Code</th>
                      </tr>
                  </thead>
                  <tbody>

                  </tbody>
              </table>
              <hr>
          </div>
      </div>
</div>

</div>
<script src="//rawgithub.com/stidges/jquery-searchable/master/dist/jquery.searchable-1.0.0.min.js"></script>
<script type="text/javascript">
$(document).ready(function(){
  $(function () {
    $( '#table' ).searchable({
        searchType: 'default'
    });
    $( '#EventTable' ).searchable({
        searchField: '#searchEvent',
        searchType: 'default'
    });
});

  var tableRef = document.getElementById('table').getElementsByTagName('tbody')[0];
  var groupName = [];
  var category = [];
  var keywords = [];
  var groupid = [];
  <?php
      require_once("config/db.php");
      $connection = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
      $groupName = array();
      $group_id = array();
      if (!$connection->connect_errno) {
        $sql = "SELECT group_name,group_id FROM a_group;";
        $query= $connection->prepare($sql);
        $query->execute();
        $query->bind_result($gn,$gid);
        while($query->fetch()){
          array_push($groupName, $gn);
          array_push($group_id, $gid);
        }
      }

      $categoryArray = array();
      $keywordArray = array();
      if (!$connection->connect_errno) {
        for($i = 0; $i < count($group_id); $i++){
        $category = array();
        $keywords = array();
        $sql = "SELECT category,keyword FROM about WHERE group_id = ?";
        $query= $connection->prepare($sql);
        $query->bind_param("i", $group_id[$i]);
        $query->execute();
        $query->bind_result($cat,$keyW);
        if (!$query) {
          printf("Errormessage: %s\n", $connection->error);
        }
        while($query->fetch()){
          array_push($category, $cat);
          array_push($keywords, $keyW);
        }
        if(count($category)==1){
          array_push($categoryArray, $category[0]);
        }else{
        for($b = 0; $b < count($category);$b++){
          if($b == 0){
            $categoryString = $category[0];
          }else{
          $categoryString = $categoryString . ', ' . $category[$b];
         }
        }
        array_push($categoryArray, $categoryString);
       }

       if(count($keywords)==1){
         array_push($keywordArray, $keywords[0]);

       }else{
       for($c = 0; $c < count($keywords);$c++){
         if($c == 0){
           $keyWordString = $keywords[0];
         }else{
         $keyWordString = $keyWordString . ', ' . $keywords[$c];
        }
       }
       array_push($keywordArray, $keyWordString);
      }
    }
  }
   ?>
  groupName = <?php echo json_encode($groupName) ?>;
  category = <?php echo json_encode($categoryArray) ?>;
  keywords = <?php echo json_encode($keywordArray) ?>;
  groupid = <?php echo json_encode($group_id) ?>;

  for(var i = 0; i < groupName.length; i++){
    var newRow   = tableRef.insertRow(tableRef.rows.length);
    var newCell1  = newRow.insertCell(0);
    var newCell2  = newRow.insertCell(1);
    var newCell3  = newRow.insertCell(2);
    var newText1  = document.createTextNode(groupName[i]);
    var newText2  = document.createTextNode(category[i]);
    var newText3  = document.createTextNode(keywords[i]);
    newCell1.appendChild(newText1);
    newCell2.appendChild(newText2);
    newCell3.appendChild(newText3);
  }



  /*---------*/


  var tableRef2 = document.getElementById('EventTable').getElementsByTagName('tbody')[0];
  var eventTitle = [];
  var eventDescription = [];
  var eventStart = [];
  var eventEnd = [];
  var eventLocationName = [];
  var eventZipCode = [];
  <?php
      $eventTitle = array();
      $eventDescription = array();
      $eventStart = array();
      $eventEnd = array();
      $eventLocationName = array();
      $eventZipCode = array();

      if (!$connection->connect_errno) {
        $sql = "SELECT * FROM an_event WHERE start_time < NOW() + INTERVAL 3 DAY AND end_time >= NOW();";
        $query= $connection->prepare($sql);
        $query->execute();
        $query->bind_result($eid,$titl,$des,$st,$et,$locn,$zcode);
        while($query->fetch()){
          array_push($eventTitle, $titl);
          array_push($eventDescription, $des);
          array_push($eventStart, $st);
          array_push($eventEnd, $et);
          array_push($eventLocationName, $locn);
          array_push($eventZipCode, $zcode);
        }
      }
   ?>
   eventTitle = <?php echo json_encode($eventTitle) ?>;
   eventDescription = <?php echo json_encode($eventDescription) ?>;
   eventStart = <?php echo json_encode($eventStart) ?>;
   eventEnd = <?php echo json_encode($eventEnd) ?>;
   eventLocationName = <?php echo json_encode($eventLocationName) ?>;
   eventZipCode = <?php echo json_encode($eventZipCode) ?>;

   for(var i = 0; i < eventTitle.length; i++){
     var newRow   = tableRef2.insertRow(tableRef2.rows.length);
     var newCell1  = newRow.insertCell(0);
     var newCell2  = newRow.insertCell(1);
     var newCell3  = newRow.insertCell(2);
     var newCell4  = newRow.insertCell(3);
     var newCell5  = newRow.insertCell(4);
     var newCell6  = newRow.insertCell(5);


     var newText1  = document.createTextNode(eventTitle[i]);
     var newText2  = document.createTextNode(eventDescription[i]);
     var newText3  = document.createTextNode(eventStart[i]);
     var newText4  = document.createTextNode(eventEnd[i]);
     var newText5  = document.createTextNode(eventLocationName[i]);
     var newText6  = document.createTextNode(eventZipCode[i]);

     newCell1.appendChild(newText1);
     newCell2.appendChild(newText2);
     newCell3.appendChild(newText3);
     newCell4.appendChild(newText4);
     newCell5.appendChild(newText5);
     newCell6.appendChild(newText6);
   }

  });
</script>
</html>
</body>
