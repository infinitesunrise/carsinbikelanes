<div id="disqus_thread"></div>
<script>
var disqus_url = '<?php echo 'http://' . $_SERVER['SERVER_NAME'] . '/index.php?single_view=' . $row[0]; ?>';
var disqus_id = disqus_url;
var disqus_config = function () {
    this.page.url = disqus_url;
    this.page.identifier = disqus_id;
};

(function() {
    var d = document, s = d.createElement('script');
    s.src = '//<?php echo $config['disqus']; ?>.disqus.com/embed.js';
    s.setAttribute('data-timestamp', +new Date());
    (d.head || d.body).appendChild(s);
})();
</script>