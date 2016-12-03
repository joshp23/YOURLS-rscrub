# YOURLS-rscrub
An HTTP referrer scrubbing swiss army knife for [YOURLS](http://yourls.org/)

Rscrub is an evolution of the [Hide Referrer](https://github.com/Sire/yourls-hide-referrer) plugin which lets you hide the HTTP referer header when redirecting. This can be useful when you wish to avoid sending sensitive querystrings to an external site, and any number of other cases. The plugin works by either adding a prefix to exististing short urls (defualt), allowing you to choose which links to scrub and which to keep in tact, or it can be made to work on all short urls.

Rscrub's primary departure from Hide Referrer is that it also (optionally) allows for the referrer scrubbing of non-shortend urls, acting in a similar fasion as such sites as [Anonymizer.info](https://www.anonymizer.info/), [href.li](https://href.li/), or [DeReferrer.Me](https://dereferer.me/). If this option is enabled then you also have the ability to download a customized javascript, `rscrub.js`, that you can install on your server and call from other apps in order to scrub an entire blog, anonymize all links coming out of a forum, or any number of other uses. You can easily run your own, trusted, anonymizing link service.

Plenty of pre-formatted examples exist in the plugin's option page and the plugin itself is very self explanatory. 

To install, simply

1. Download this repo and extract the rscrub folder to your YOURLS/user/plugins/ folder.
2. Enable the plugin in the Admin seciton of YOURLS
3. Go to the rscrub settings page and make sure everything looks right.
4. Optionally download `rscrub.js` to your server (rscrub for YOURLS will generate this file for you), and follow the instructions to add it to your apps.

#### NOTE: Using HTTPS on all redirects ensures a hidden referrer on all browsers tested, however when using HTTP results may vary. In this case, some browsers will always show the last referrer, which will be the YOURLS installtion. Therefore HTTPS is the recommended method. Trusted SSL certificates for HTTPS can be obtained from [Let's Encrypt](https://letsencrypt.org/) for free.
