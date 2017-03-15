Bolt TinyPNG and TinyJPG API Extension
======================

Bolt Extension that uses TinyPNG and TinyJPG api to optimize images in your `files` directory

## Setup

You'll need to get an API key from TinyPNG. If you don't have one you can get one from:  

https://tinypng.com/developers  

The free account will allow you to make 500 (five hundred) image compressions per month. 

Once you have your key enter it into the extensions config under 'tinypng_apikey':  

```yaml
tinypng_apikey: 'your_key_here'
```

Once your key is setup visit your dashboard and either hover over 'Extras' and click "TinyPNG Image Optimization" or visit while logged into your site's dashboard:  [/bolt/extend/tinypng](/bolt/extend/tinypng)  

__Upload, Resize & Optimize Images__  

In the back end page you'll also be able to upload images, restrict those images to a specific size and save those to your files directory. 

In order to do this you'll need to provide at least 2 (two) configuration settings. You'll need to provide a resize method and at minimum depending on your resize method a width. To see the available resize methods please see [TinyPNG Documentaion on Resizing](https://tinypng.com/developers/reference/php#resizing-images).  

Here is the default config for resizing images:  

```yaml
tinypng_upload:
  method: 'scale'
  width: 1000
```  

For every image uploaded through the tinypng extensions backend will be resized with the "scale" method - this resizes the image down proportionally - to a max width of 1000px wide. So for example an image with the dimensions of ``1024x768`` will be scaled to ``1000×750``  
  
If you choose either `fit` or ``cover`` as a resize method you __must__ provide and with and height.  


A Note On Compression Count with Resizing:  

>Resizing counts as one additional compression. For example, if you upload a single image and retrieve the optimized version plus 2 resized versions this will count as 3 compressions in total.  


__Notes On Uploaded Images__

* If your filename contains spaces those spaces will be converted to underscores.   
  * example:  ``a file with spaces.jpg => a_file_with_spaces.jpg ``  
  
* if the image already exists in your `files` directory then a timestamp will be appended to the image with the YearMonthDay_UniqueID.  
  * example: ``heyo.jpg`` exists in files. Newly uploaded ``heyo.jpg`` becomes ``heyo_20170315_58c96e5664790.jpg``



### Help:  
if you're using Nginx and get a 504 Gateway timeout you may need to add a section to your Nginx sites-available conf then reload nginx. The portion you'll need to add is ``fastcgi_keep_conn on;``:  

```nginx
location ~ \.php$ {  
  # the other php location block settings...
  fastcgi_keep_conn on; # this is what you'll more than likely need to add
}
```  

If your upload fails with the message:  

``TinyPNG File upload failed:: ERROR: The file is too large. Allowed maximum size is 2 MiB.``  

You'll need to adjust your upload settings found in your sites ``php.ini``. You'll need to adjust:  

```ini
; These are the default settings
; you'll need to adjust these to your prefered settings... 

; Maximum allowed size for uploaded files.
upload_max_filesize = 2M

; Must be greater than or equal to upload_max_filesize
post_max_size = 8M
```  

If you're using Nginx and you get a gateway timeout after adjusting these.. You'll need to either add or update the `client_max_body_size`  

```nginx 
http {  
  
  # other stuff ...
  # client_max_body_size yourAdjustSize;
  # example:  
  client_max_body_size 100m;
}
```  

See https://easyengine.io/tutorials/php/increase-file-upload-size-limit/ and https://www.scalescale.com/tips/nginx/504-gateway-time-out-using-nginx/ for additional help not found here.  

For Apache see: https://www.devside.net/wamp-server/apache-and-php-limits-and-timeouts


Panda image by: <a title="By Creative Tail [CC BY 4.0 (http://creativecommons.org/licenses/by/4.0)], via Wikimedia Commons" href="https://commons.wikimedia.org/wiki/File%3ACreative-Tail-Animal-panda.svg">Creative Tail</a>