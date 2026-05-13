<script>
    var jsonResponse = <?php echo json_encode($request_all) ?>;
    if (window.FlutterChannel) {
      FlutterChannel.postMessage(JSON.stringify(jsonResponse));
    }
</script>