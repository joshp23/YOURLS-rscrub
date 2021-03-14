# YOURLS-rscrub
An HTTP referrer scrubbing swiss army knife for [YOURLS](http://yourls.org/)

## What is it?
Rscrub is an evolution of the [Hide Referrer](https://github.com/Sire/yourls-hide-referrer) plugin which lets you hide the HTTP referer header when redirecting. This can be useful when you wish to avoid sending sensitive querystrings to an external site, and any number of other cases. The plugin works by either adding a prefix to exististing short urls (defualt), allowing you to choose which links to scrub and which to keep in tact, or it can be made to work on all short urls.
## What sets it apart from its predecessor?
Rscrub's primary departure from Hide Referrer is that it also (optionally) allows for the referrer scrubbing of non-shortend urls, acting in a similar fasion as such sites as [Anonymizer.info](https://www.anonymizer.info/), [href.li](https://href.li/), or [DeReferrer.Me](https://dereferer.me/). If this option is enabled then you also have the ability to download a customized javascript, `rscrub.js`, that you can install on your server and call from other apps in order to scrub an entire blog, anonymize all links coming out of a forum, or any number of other uses. You can easily run your own, trusted, anonymizing link service.

Also, Rscrub has seamless [Snapshot](https://github.com/joshp23/YOURLS-Snapshot) integration. There is a problem of having no preview and no identifying information appear when posting scrubbed links to certain social media sites, Snapshot integration addresses this problem by serving up a locally stored image preview of the site, along with the proper headers, to give social media sites something to present when posting.  

Plenty of pre-formatted autoamtion examples exist in the plugin's option page, and the plugin itself is very self explanatory. 
## Installation
1. Download this repo and extract the rscrub folder to your YOURLS/user/plugins/ folder.
2. Enable the plugin in the Admin seciton of YOURLS
3. Go to the rscrub settings page and make sure everything looks right.
4. Optionally download `rscrub.js` to your server (rscrub for YOURLS will generate this file for you), and follow the instructions to add it to your apps.

### Special instruction for subdmain use in Apache
It is possible to use subdomains with Rscrub so that you do away with the prefix in the URL. I find this more intuitive. For the time being, you still need to set a prefix in order for this to work. 

* You need the subdomains to be aded as aliases in your vhost.conf file, so it should look something like the following:
```
	ServerName sho.rt
	ServerAlias anon.sho.rt
	ServerAlias a.sho.rt
```

* Then you have to edit htaccess. The following example uses the default prefix settings of `+` for long urls and `@` for shortened urls; it also uses the subdomains from above, `anon.sho.rt` for long and `a.sho.rt` for short. Add the following to the top of your htaccess file, before any other rewrite rules:
```
## REWRITE RULES
RewriteEngine On

# RSCRUB
RewriteCond %{HTTP_HOST} ^a(non)?\. [NC]
RewriteRule ^(https?://?)(.*)$ https://sho.rt/@https://$2 [P]
RewriteCond %{HTTP_HOST} ^a(non)?\. [NC]
RewriteCond %{REQUEST_URI} !^https?://.* [NC]
RewriteRule (.*)$ https://sho.rt/@$1 [P]

```
With this `example.com` is scrubbed by entering `https://anon.sho.rt.com/http://example.com` 

and `sho.rt/keyword` is scrubbed by entering `https://a.sho.rt/keyword`.

Please note: If you are using SSL (as in the htaccess example above) make sure that you have `SSLProxyEngine on` in your vhost config. If not, simply change `https://` to `http://` in the RewriteRule.

#### IMPORTANT NOTE: 
  Using HTTPS on all redirects ensures a hidden referrer on all browsers tested, however when using HTTP results may vary. In this case, some browsers will always show the last referrer, which will be the YOURLS installtion. Therefore _forcing HTTPS is the recommended method_. Trusted SSL certificates for HTTPS can be obtained from [Let's Encrypt](https://letsencrypt.org/) for free.
#### DISCLAIMERS:
* This plugin is offered "as is", and may or may not work for you. Give it a try, and have fun!
* The referrer header is controlled by the web browser, so methods used in here can stop working at any time.

### Tips
Dogecoin: DARhgg9q3HAWYZuN95DKnFonADrSWUimy3

===========================

    Copyright (C) 2016 Josh Panter

    This program is free software: you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation, either version 3 of the License, or
    (at your option) any later version.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program.  If not, see <http://www.gnu.org/licenses/>.
