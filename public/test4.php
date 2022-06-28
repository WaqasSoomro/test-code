<html>

<head>
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
    <!--<script src="https://cdn.socket.io/socket.io-1.0.0.js"></script>-->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/socket.io/2.3.0/socket.io.js"></script>
</head>

<body>

<button id="quickstart">quickstart</button>
<button id="quickstop">quickstop</button>
<button id="status">status</button>

<script>

var socket;


$(document).ready(function () {
   //socket = io.connect('https://live.humanox.com:10357');
   socket = io.connect('https://trainer.jogo.ai:3031');
   
    //  socket.on('quickstart', function(match_id){
    //     console.log(match_id);
    // });

    $('#status').click(function(){
        alert('status');
        console.log(socket.emit( 'status', '862549047831667' ),'status');
    });

    $('#quickstart').click(function(){
        alert('quickstart');
         console.log(socket.emit( 'upgrade_firmware_emitted', '862549047831667' ),'quickstart');
    
    });

    $('#quickstop').click(function(){
        alert('quickstop');
        console.log(socket.emit( 'quickstop', '862549047831667' ), 'quickstop');
    });

});

</script>

</body>
</html>