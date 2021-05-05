# productvideos
ThirtyBees module for adding videos to your product (shows up in a product tab)


## Hooks
This ties into the `displayProductTabContent` hook to automatically display as a tab on the page of a product.

**It is expected that you will override `productvideos.tpl` to make it how your theme expects tabs**

If you do not want the video to be in a tab, you just need to remove the hook from the "Modules and Services" tab in the back office.

You will then be able to use  `{hook h='displayProductVideosTab' product=$product}` in your theme to get the product videos anywhere in your `product.tpl` file in your theme.

## How to Add Videos
In the back office when you edit a product you will see a new tab on the left called "Product Videos" in that tab you will be able to add and remove videos based on their URL.

Currently this module does not support uploading videos. Only URLs such as YouTube, and Vimeo.