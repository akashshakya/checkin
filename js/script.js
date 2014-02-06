//@author Bradly Schlenker
$(document).ready(function(){
$("#nonefound").hide();


//if(checkin.php) essentially
if($("#eventName").length > 0){
    $("#myModal").on('hide.bs.modal', function(){
        $(".paymentArea").removeClass("has-success has-feedback");
        $(".glyphicon").remove();
        $("#myModal").find(".alert").alert('close');
    });
    $("#modalMoney").on("propertychange keyup input paste", function(event){
        $("#paymentAmount").val($(this).val());
    });
    $(".modalMoneyClearer").each(function() {
        $(this).on("click select", function( event) {
            $("#modalMoney").val($(this).text());
            $("#paymentAmount").val($(this).text());
        });
    });
    $.post("search.php", { purpose : 'getEvent', eventid : $("#eventID").val() }, function(data) {
        $("#eventName").html(data);
    });
    $("#save").on("click", function() {
        var money = $("#paymentAmount").val();
        var email = $("#modalEmail").val();
        var name = $("#modalName").val();
        var cid = $("#myModal").find(".cid").val();
        var eventid = $("#eventID").val();
        var checkout = false;
        money = money.replace('$', '');
        $("#myModal").find(".alert").alert('close');
        $.post("search.php", { purpose : "checkin", eventid : eventid, money : money, email : email, name : name, cid : cid, checkout : checkout}, function(data){
            if(data){
                $("#myModal").find("#result").append(makeAlertBox(data));
            }
            else{
                $("#myModal").modal('hide');
                $("#search").val(name);
                updateSearchResults(name);
            }
        });
    });
    $('#search').each(function() {
        var elem = $(this);
        // Save current value of element
        elem.data('oldVal', elem.val());
        // Look for changes in the value
        elem.bind("propertychange keyup input paste", function(event){
            // If value has changed
            if (elem.data('oldVal') !== elem.val() || elem.val() === '') {
                // Updated stored value
                elem.data('oldVal', elem.val());
                // Do action
                updateSearchResults(elem.val());
            }
        });
    });
    updateSearchResults("");
}

//if(events.php)
if($("#organizationName").length > 0){
    $.post("search.php", { purpose : 'getOrganization', organizationid : $("#organizationID").val() }, function(data) {
        $("#organizationName").html(data);
    });
    $("#myModal").on('hide.bs.modal', function(){
        $("#myModal").find(".alert").alert('close');
    });
    $("#eventSearch").each(function() {
        var elem = $(this);
        // Save current value of element
        elem.data('oldVal', elem.val());
        // Look for changes in the value
        elem.bind("propertychange keyup input paste", function(event){
            // If value has changed
            if (elem.data('oldVal') !== elem.val() || elem.val() === '') {
                // Updated stored value
                elem.data('oldVal', elem.val());
                // Do action
                updateEventSearchResults(elem.val());
            }
        });
    });
    $("#save").on("click", function() {
        var name = $("#modalName").val();
        var eventID = $("#eventID").val();
        var organizationID = $("#organizationID").val();
        var checkout = false;
        $("#myModal").find(".alert").alert('close');
        $.post("search.php", { purpose : "editEvent", eventid : eventID, name : name, organizationid : organizationID, checkout : checkout}, function(data){
            if(data){
                $("#myModal").find("#result").append(makeAlertBox(data));
            }
            else{
                if(eventID){
                    $("#myModal").find("#result").append(makeSaveEventSuccessBox());
                }
                else{
                    $("#myModal").modal('hide');
                    updateEventSearchResults($("#eventSearch").val());
                }
            }
        });
    });
    updateEventSearchResults("");
    
}

function makeSaveEventSuccessBox(){
    var box = '<div class="alert alert-success alert-dismissable" id="modalSuccess"> \n\ \
               <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button> \n\ \
               <strong>Success!</strong> You\'re a real champion at editing! \n\ \
               </div>';
    return box;
}

//Returns a div that is formatted to display whatever data is in an error format
function makeAlertBox(data){
    var alert = '<div class="alert alert-danger fade in" id="modalProblem">\n\ \
                <button class="close" aria-hidden="true" data-dismiss="alert" type="button">\n\ \
                ×\n\ \
                </button>\n\ \
                <h4>\n\ \
                Oh snap! You got an error!\n\ \
                </h4>\n\ \
                <p>\n\ ' + data +
                '\n\ \
                </p>\n\ \
                </div>';
    return alert;
}
//Loads up myModal for content on checkins
function loadupModal(customerElem){
    var name = customerElem.find("#username").text();
    if(!name || name === 'Add New User'){
        name = $("#search").val();
    }
    $("#modalTitle").text("Checking in " + name);
    $("#modalName").val(name);
    
    var cid = customerElem.find(".cid").text();
    if(cid){
        var email = customerElem.find(".email").text();
        $("#modalEmail").val(email);
        var payment = customerElem.find(".payment").text();
        $("#paymentAmount").val(payment);
        if(payment){
            $(".customMoney").append('<span class="glyphicon glyphicon-ok form-control-feedback" style="right:0px;"></span>');
            $(".paymentArea").addClass("has-success has-feedback");
        }
        $("#modalMoney").val(payment);
        $("#myModal").find(".cid").val(cid);
    }
    else{
        $("#modalEmail").val("");
        $("#paymentAmount").val("");
        $("#modalMoney").val("");
        $("#myModal").find(".cid").val("");
    }
    $("#myModal").modal('show');
}


function loadupEventModal(eventElem){
    var name = eventElem.find("#eventResultName").text();
    var modalTitleText;
    if(name === 'Add New Event'){
        modalTitleText = "Adding";
    }
    else{
        modalTitleText = "Editing";
    }
    if(!name || name === 'Add New Event'){
        name = $("#eventSearch").val();
    }
    $("#modalTitle").text(modalTitleText + " event " + name);
    $("#modalName").val(name);
    
    var eventResultID = eventElem.find(".eventResultID").text();
    if(eventResultID){
        $("#eventID").val(eventResultID);
        $("#gotoEvent").show();
        $("#gotoEvent").attr("href", "checkin.php?id=" + eventResultID);
    }
    else{
        $("#gotoEvent").hide();
    }
    $("#myModal").modal('show');
}

//Used for the checkin.php page
function updateSearchResults (name){
    $.post("search.php",
        { purpose : "searchCustomers", name : name, eventID : $("#eventID").val() },
        function ( data ) {
            $("#beforefound").hide();
            $(".customer").remove();
            if(data){
                $("#result").prepend(data);
                $(".customer").on("click", function ( event ) {
                    loadupModal($(this));
                });
                $(".customer").mouseover(function (event ){
                    $(this).addClass("border-highlight");
                });
                $(".customer").mouseout(function (event ){
                    $(this).removeClass("border-highlight");
                });
                $("#nonefound").hide();
            } else{
                $("#nonefound").show();
            }
    });
}

//Used for the events.php page
function updateEventSearchResults (name){
    $.post("search.php",
        { purpose : "searchEvents", name : name, organizationID : $("#organizationID").val() },
        function ( data ) {
            $("#beforefound").hide();
            $(".eventResultItem").remove();
            if(data){
                $("#eventResultArea").prepend(data);
                $(".eventResultItem").on("click", function ( event ) {
                    loadupEventModal($(this));
                });
                $(".eventResultItem").mouseover(function (event ){
                    $(this).addClass("border-highlight");
                });
                $(".eventResultItem").mouseout(function (event ){
                    $(this).removeClass("border-highlight");
                });
                if($(".eventResultID").length > 0){
                    $("#nonefound").hide();
                } else {
                    $("#nonefound").show();
                }
            }
    });
}

});