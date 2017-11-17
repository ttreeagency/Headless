# Headless API for Neos CMS

## Description

This package is highly experimental, don't use it at home.

Lots of inspiration and foundation taken from from [Wwwision.Neos.GraphQL](https://github.com/bwaidelich/Wwwision.Neos.GraphQL) and the API
took a lots of inspiration from the Simple API of [GraphCMS](https://graphcms.com/docs/api_simple/).

## Goals

The goal of the package is to create a Domain centric GraphQL API. The queries/mutations semantics are automatically
generated from Node Types definitions and currently look like:

```
{
  NeosNeosNamespace {
    allShortcuts {
      id
      createdAt
      updatedAt
      title
      targetMode
      target
    }
  }
  NeosDemoNamespace {
    Chapter(identifier: "23f35ab0-0d82-21e7-0006-dd63c5b3dce8") {
      title
    }
    allChapters {
      id
      title
      chapterDescription
    }
    allHomepages {
      title
      layout
      subpageLayout
    }
  }
}
```

## System Property

- `id`: the node identifier
- `createdAt`: node creation time
- `updatedAt`: node last modification time

### Roadmap

The initial goal is to have a read only API, the next step will to add mutation support.

#### 1.0
- [x] Automatic query generation based on the node configuration
- [ ] Content Collection support
- [ ] Images support
- [ ] Assets support
- [ ] Reference(s) support
- [ ] Pagination support
- [ ] More advanced API, like a good support for Facebook Relay

#### 1.2

- [ ] Management of `Permanent Auth Token` to access to API
- [ ] An API to register custom query generation per node type

#### 2.0

- [ ] Intelligent cache layer with auto flushing
- [ ] Automatic mutations generation based on the node configuration
- [ ] An API to register custom query not directly attached to a node type

#### 3.0

- [ ] Mutation support with fine grained access permissions (CRUD configuration per node type and per property)

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
