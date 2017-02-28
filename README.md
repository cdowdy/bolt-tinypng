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