# Headless API for Neos CMS

## Description

This package is highly experimental and not working currently, don't use it at home.

Most of the code stollen from [Wwwision.Neos.GraphQL](https://github.com/bwaidelich/Wwwision.Neos.GraphQL) and the API
took a lots of inspiration from the Simple API of [GraphCMS](https://graphcms.com/docs/api_simple/).

## Goals

Building a human frienldy API to access content nodes from the Content Repository, with queries 
that use the semantic of the content. 

### Todos

The initial goal is to have a read only API, the next step will to add mutation support.

- [ ] Automatic query generation based on the node configuration
- [ ] An API to register custom query generation per node type
- [ ] An API to register custom query not directly attached to a node type
- [ ] Management of `Permanent Auth Token` to access to API
- [ ] Intelligent cache layer with auto flushing
- [ ] Pagination support
- [ ] Mutation support with fine grained access permissions (CRUD configuration per node type and per property)
- [ ] More advanced API, like a good support for Facebook Relay

### Examples

By example if you use the Neos CMS demo site. This package include a Chapter node type. 

So a query to get a specific chapter should look like this:

```graphql
{
  Chapter(identifier: "6db34628-60c7-4c9a-f6dd-54742816039e") {
    title
    description
    createdAt
    
    # Get all the content of the given collection, recursively
    _collection(path: "main", baseType: "Neos.Neos:Content") {
      _type
      title
      text
    }
    
    # Single image from the node property "image"
    image {
      fileName
      url
      mimeType
      size
    }
    
    # One or more images from the node property "gallery"
    gallery {
      fileName
      url
      mimeType
      size
    }
  }
}
```

To get all chapters:

```graphql
{
  allChapters(limit: 20) {
    title
    description
    createdAt
    
    # Get all the content of the given collection, recursively
    _collection(path: "main", baseType: "Neos.Neos:Content") {
      _type
      title
      text
    }
    
    # Single image from the node property "image"
    image {
      fileName
      url
      mimeType
      size
    }
    
    # One or more images from the node property "gallery"
    gallery {
      fileName
      url
      mimeType
      size
    }
  }
}
```

## Sponsors & Contributors

The development of this package is sponsored by ttree (https://ttree.ch).
