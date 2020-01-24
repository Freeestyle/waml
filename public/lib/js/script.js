// va chercher la class list-of-states
var wordStates = document.querySelectorAll(".list-of-states li a ");
var svgStates = document.querySelectorAll("#states a > *");
function removeAllOn() {
    wordStates.forEach(function(el) {
        el.classList.remove("on");
    });
    svgStates.forEach(function(el) {
        el.classList.remove("on");
    });
}
function addOnFromList(el) {
    var stateCode = el.getAttribute("data-state");
    var svgState = document.querySelector("#" + stateCode);
    el.classList.add("on");
    svgState.classList.add("on");
}
function addOnFromState(el) {
    var stateId = el.getAttribute("id");
    var wordState = document.querySelector("[data-state='" + stateId + "']");
    el.classList.add("on");
    wordState.classList.add("on");
    console.log(wordState);
}
wordStates.forEach(function(el) {
    el.addEventListener("mouseenter", function() {
        addOnFromList(el);
    });
    el.addEventListener("mouseleave", function() {
        removeAllOn();
    });
    el.addEventListener("touchstart", function() {
        removeAllOn();
        addOnFromList(el);
    });
});
svgStates.forEach(function(el) {
    el.addEventListener("mouseenter", function() {
        addOnFromState(el);
    });
    el.addEventListener("mouseleave", function() {
        removeAllOn();
    });
    el.addEventListener("touchstart", function() {
        removeAllOn();
        addOnFromState(el);
    });
});


(function($) {
   $(document).ready(function(){
       $('#Bt-registration-submit').click(function(e){
           $pwlength = $('#registration_form_plainPassword_first').val().length;
           if($pwlength<5){
               alert('le mot de passe doit avoir minimum 5 caractères');
               e.preventDefault();
               return;
           }
       });
   });
}(jQuery));

