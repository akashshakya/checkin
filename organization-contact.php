<?php

require_once 'backend/html.php';
require_once 'backend/organization.php';
require_once 'backend/checkin.php';
require_once 'backend/settings.php';
require_once 'backend/event.php';

printHeader();

if(isset($_GET['id'])){
    $id = $_GET['id'];
    if(!isInteger($id) || $id < 1){
        echo "Need a positive integer for id";
        exit;
    }
}

$isFreeEntranceEnabled = isFreeEntranceEnabled(inferOrganizationID($id));

?>
<div class="container">
    <div class="starter-template">
        <h1><span id="eventName"></span></h1>
        
        
        
        
        
        
        
        
        
        
    </div>
</div>
<?php

printFooter();