Caching demo
============

The purpose of this module is to demonstrate Drupal 8 caching metadata and how it works. There are several tags that correspond with progres of writing a basic module, and then slowly adding the necessary caching data to make it function correctly.

The demo module's functionality is just to show related items based on an taxonomy field 'field_universe' on node pages. The tags correspond with each section of progress.

**P.S. Yes, I know this could easily be replicated with the Views module in a few minutes, but that wouldn't provide a great example for demonstrating Drupal 8's caching metadata.**

### chapter-1

The module works! (or so it appears.) Our intrepid developer has written code that successfully queries the database for additional nodes matching the current node's taxonomy term, and displays them in a block.

But what happens when we look at another page? Why does looking at another node with a different term display items from the first node we looked at??

### chapter-2

Looks like we just needed to add a cache context to make sure that the block is varied by path. Now it shows the correct data on every page!

Oops! We found a typo in the title of one of the related items, so we fixed it and saved the node with the typo. But when we look at the original page, the typo is still there. What now??

### chapter-3

Adding cache tags for each node certainly helps! Now whenever we update a node, any block that lists that node will have it's cache invalidated automatically! Yay!

But what happens when a node has the wrong category on it, and it's related block show the wrong category? Updating the current node doesn't update the block of the page we're on.

### chatper-4

Adding the cache tag for the current node seems to do the trick! Now any change to the node will update the block on that page as well!

But what happens when a new item is added? How do we ensure that the block reflects new items? The block can't be tagged with the cache tag from a node that doesn't exist yet?

### chapter-5

Ahh now our block updates whenever the node list is updated! Perfect!
