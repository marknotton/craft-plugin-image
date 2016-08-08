# Images *for Craft CMS*

Images adds a small collection of filters to help manage reoccurring image queries.

### After installation
When this plugin is installed, an assets field type called "featured image" will be added to the default field types.  

## Images

This filter will query an asset field type and list out all the available image assets as per the settings.
> If you just want to get one single image, you can use "image" instead of "images".

### Usage:
When using the image filter on a entry, you must define the field type handle.
```
{{ entry|images('fieldHandle', transformType )}}
{{ entry|images('fieldHandle', transformType, { settings... })}}
{{ entry|images('fieldHandle', { settings... })}}
```
When using the images filter directly on an image field type, you do not need to pass the field type handle
```
{{ entry.gallery|images({ settings... })}}
{{ entry.gallery|images('transformType')}}
{{ entry.gallery|images(transformType, { settings... })}}
```
If the transform type is passed as a string, this will use a predefined [image transform](https://craftcms.com/docs/image-transforms). However, you can define an array of transforms settings directly too.

### Settings:
| Option    | Example                 | Type             | Description
 ---------- | ----------------------- | ---------------- | ------------------
| transform | thumb                | String or Array  | Define an transform type
| class     | 'pic-%i'               | String           | Define a class for the image element. Use '%i' if you want the numbered items
| id        | 'id-%i'                | String           | Define an id for the image element. Use '%i' if you want the numbered items
| data      | ['img', %id]           | Array            | Define an data attribute for the image element. First array element will be the data attribute name. The second will be the value. Use '%id' if you want the asset ID
| element   | 'image'                | String           | Define what element tags the image will use. "img" and "image" great a real image. Anything else will define a background iamge
| size      | false                  | Bool             | If true, the images dimensions will be added. If Width or Heigh are defined, the define options will overwrite the real this
| width     | '100%'                 | String or Number | Set the width.
| height    | 555                    | String or Number | Set the height.
| url       | true                   | Bool             | Return a url or array of urls
| shuffle   | true                   | Bool             | Alias of order:'RAND()';
| order     | 'RAND()'               | String           | https://craftcms.com/docs/templating/craft.assets#order
| limit     | 4                      | Number           | Limit number of images
| svg       | true                   | Bool             | If true, SVG images will be extracted as HTML. When SVG's are used, only the 'wrap', 'limit', 'shuffle' settings will apply
| wrap      | ['li div', 'pic-%i']   | String or Array  | Requires the [wrapper plugin](https://github.com/marknotton/craft-plugin-wrapper)
| fallback  | true                   | Bool or String   | See Below
**Fallback option:**
If true and a fallback image is required, the field handle will be used to look for an image in the image directory that is prefixed with *'default-'.* Example, if the field handle was '*featured*' this image will be used: '*default-featured.svg*'. All image extensions will be searched in this order: svg, png, jpg, gif. First file to exists wins.

If a string is passed, that string will be used instead of the field handle.

False, will not return any fallback and not load any image at all.

## Image information

You can grab useful information about an image file too.

###Usage:
Image files being queried must be local in order for the data to be retrieved. The current working directory is automatically used if one isn't found. So no need for absolute paths.

Also, if you have defined your image directory in your [environment variables](https://craftcms.com/docs/multi-environment-configs) config file; this will be queried first.

| Filter      | Type    | Description
| ----------- | ------- | ----------------
| imageinfo   | Array   | Returns an array of all image information listed below
| width       | Number  | Width (in pixels)
| height      | Number  | Height (in pixels)
| format      | String  | Image format, example: png, jpg, gif, svg, etc...
| filesize    | String  | Image file size in it's relative format (b, kb, mb, gb)
| orientation | String  | Compares width with height and returns portrait, landscape, or square.
| ori         | String  | Same as "orientation"

```
{{ ('logo.png')|imageinfo }}
{{ ('logo.png')|width }}
{{ ('logo.png')|height }}
{{ ('logo.png')|format }}
{{ ('logo.png')|filesize }}
{{ ('logo.png')|orientation }}
{{ ('logo.png')|ori }}
```

##TODO:

- Allow the customisation of the user's environment variables naming convention.
- Allow the customisation of the fallback default prefix.
