//  This just checks to see if jQuery is already loaded
//  If it is not, we will load it from google's CDN

  if ((typeof jQuery) == 'undefined')
  {
     document.write('<script type=\"text/javascript\" src=\"http://ajax.googleapis.com/ajax/libs/jquery/1.3.2/jquery.min.js\"><\/script>');
  }