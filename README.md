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



Help:  
if you're using Nginx and get a 504 Gateway timeout you may need to add a section to your Nginx sites-available conf then reload nginx. The portion you'll need to add is ``fastcgi_keep_conn on;``:  

```nginx
    location ~ \.php$ {
        # the other php location block settings...
        fastcgi_keep_conn on; # this is what you'll more than likely need to add
    }
```


Panda image by: <a title="By Creative Tail [CC BY 4.0 (http://creativecommons.org/licenses/by/4.0)], via Wikimedia Commons" href="https://commons.wikimedia.org/wiki/File%3ACreative-Tail-Animal-panda.svg">Creative Tail</a>