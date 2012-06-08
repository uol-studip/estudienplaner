<div id="file-uploader">
    
</div>
<script>
var uploader = new qq.FileUploader({
    // pass the dom node (ex. $(selector)[0] for jQuery users)
    element: document.getElementById('file-uploader'),
    // path to server-side upload script
    action: 'dispatch.php?'
});
</script>