<?php
/**
 * 
 * @author Bradly Schlenker
 */
require_once 'backend/html.php';
require_once 'backend/settings.php';
require_once 'backend/checkin.php';
require_once 'backend/event.php';

if(isset($_GET['id']) && isInteger($_GET['id']) && $_GET['id'] > 0){
    $id = mysql_real_escape_string($_GET['id']);
} else {
    die;
}

$isFreeEntranceEnabled = isFreeEntranceEnabled(inferOrganizationID($id));
$eventName = getEventName($id);

printHeader();
?>
<div class="container">

  <div class="starter-template">
    <h1>Check-in for <span id="eventName"><?=$eventName?></span></h1>
    <div class="panel panel-default">
        <div class="panel-heading">
          <div class="row">
            <div class="col-lg-6">
                <input type="text" class="form-control" id="search" placeholder="Enter Name" autocomplete="off" />
            </div>
          </div>

        </div>
        <?php
        if($id){
        ?>
        <div class="panel-body" id="result">
            <p class="lead" id="beforefound">Go ahead and try searching for a user!</p>
            <p class="lead" id="nonefound">No users were found!</p>
            <span type='hidden' id='eventID'><?=$id?></span>
        </div>
    </div>
  </div>
  <?php
        }
        else{
            ?>
            <div class="panel-body">
                <p class="lead has-warning">I don't know how you got here, but you did it wrong!</p>
            </div>
            <?php
        }
  ?>
</div>
<div class="modal fade bs-modal-lg" id="myModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" id ="modalCloseTop" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
        <h4 class="modal-title" id="modalTitle"></h4>
      </div>
      <div class="modal-body">
      <span class="cid"></span><span id="modalCheckinID"></span>
      <div class="panel panel-default">
      <div class="panel-heading">
        <div class="row">
          <div class="col-sm-6">
            <div class="input-group-sm">
              <div class="panel-heading modalNameEmail">Name</div>
              <input type="text" class="form-control modalNameEmailInput" id="modalName" autofocus="" required="" placeholder="Enter Name" autocomplete="off" />
            </div>
          </div>
          <div class="col-sm-6">
            <div class="input-group-sm">
                <div class="panel-heading modalNameEmail">Email</div>
              <input type="email" class="form-control modalNameEmailInput" id="modalEmail" autofocus="" required="" placeholder="Enter Email" autocomplete="off" />
            </div>
          </div>
        </div>
      </div>
      <div class="panel-body" id="result">
        <div class="row">
          <div class="panel panel-default col-sm-6 col-md-3 payment-box">
              <div class="panel-heading">
                  Payment
              </div>
              <div class="panel-footer">
                  <form class="form-horizontal" role="form">
                      <div class="form-group paymentArea">
                          <div class="row">
                              <div class="col-xs-3"></div>
                              <div class="btn-group col-xs-6 money-btn-row">
                                  <div class="row">
                                  <div class="col-xs-6 customMoney">
                                      <input type="text" class="form-control" id="modalMoney" placeholder="$XX" autocomplete="off" />
                                  </div>
                                  <div class="col-xs-3 money-btn">
                                      <button type="button" class="btn btn-default modalMoneyClearer">$0</button>
                                  </div>
                                  <div class="col-xs-3 money-btn">
                                      <button type="button" class="btn btn-default modalMoneyClearer">$3</button>
                                  </div>
                                  </div>
                              </div>
                              <div class="col-xs-3"></div>
                          </div>
                          <div class="row">
                              <div class="col-xs-3"></div>
                              <div class="btn-group col-xs-6 money-btn-row">
                              <div class="row">
                                  <div class="col-xs-3 money-btn">
                                      <button type="button" class="btn btn-default modalMoneyClearer">$5</button>
                                  </div>
                                  <div class="col-xs-3 money-btn">
                                      <button type="button" class="btn btn-default modalMoneyClearer">$6</button>
                                  </div>
                                  <div class="col-xs-3 money-btn">
                                      <button type="button" class="btn btn-default modalMoneyClearer">$7</button>
                                  </div>
                                  <div class="col-xs-3 money-btn">
                                      <button type="button" class="btn btn-default modalMoneyClearer">$8</button>
                                  </div>
                              </div>
                              </div>
                              <div class="col-xs-3"></div>
                          </div>
                      </div>
                  </form>
              </div>
          </div>
          <div class="col-sm-6 col-md-3 birthday-box">
            <label for="modalDate">Birthday</label>
            <div class='input-group date' id="modalDate">
              <input type='text' class="form-control" id="modalDateForm" data-date-pickTime="false" data-date-format="YYYY-MM-DD" />
              <span class="input-group-addon"><span class="glyphicon glyphicon-calendar"></span></span>
            </div>
          </div>
          <div class="col-sm-6 col-md-3"></div>
          <div class="col-sm-6 col-md-3">
          <?php
          if($isFreeEntranceEnabled){ ?>
          <div class="panel panel-default payment-options-box">
              <div class="form-group payment-options-area">
                  <div class="checkbox useFreeEntrance">
                      <label>
                          <input type="checkbox" id="useFreeEntrance" /> Use Free Entrance
                      </label>
                  </div>
                  <div class="numberOfFreeEntrances">
                      <label for="numberOfFreeEntrances">Number Of Free Entrances</label>
                      <input type="text" class="form-control" id="numberOfFreeEntrances" placeholder="0" />
                  </div>
              </div>
          </div>
          <?php
          }
          ?>
          </div>
      </div>
      </div>
      </div>
      <div class="modal-footer">
        <button type="button" id="modalCheckout" class="btn btn-danger">Checkout</button>
        <button type="button" id ="modalCloseBot" class="btn btn-default" data-dismiss="modal">Close</button>
        <button type="button" class="btn btn-primary" id="save">Save & Checkin</button>
      </div>
    </div>
  </div>
</div>
</div>

<?php
printFooter();
?>