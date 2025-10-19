# Laravel Livewire data source for Clockwork

## Features

TODO

## Getting started

TODO

### Livewire events and arguments

These are the events that Livewire dispatches and the attributes for each event

- `mount` - $component, $params, $key, $parent
- `render` - $component, $view, $properties
- `call` - $root, $method, $params, $context, $returnEarly
- `update` - $component, $path, $value
- `hydrate` - $component, $memo, $context
- `dehydrate` - $component, $context
- `destroy` - $component, $context
- `profile` - $event, $id, $timings(start, finish) - (only dispatched with `app.debug = true`)

#### Loading a page with a Livewire component
- mount
- render
- dehydrate

#### Updating a model in a Livewire component
- hydrate
- update
- render
- dehydrate

#### Call a method on a  Livewire component
- hydrate
- call
- render
- dehydrate