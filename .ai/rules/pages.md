---
paths:
  - 'app-modules/*/resources/views/filament/pages/**'
---

# Pages

## Custom Filament/Livewire page views need exactly one root element
A Livewire component's Blade view must render a single top-level element. Livewire injects `wire:id`/`wire:snapshot` onto whatever it finds as the first root tag — if a directive like `@vite(...)` sits as a sibling *before* `<x-filament-panels::page>`, that attribute can end up on the wrong element, and any nested `x-data="..."` component loses access to `$wire` (every property/method access silently returns a no-op `() => {}` instead of throwing, which is very hard to debug).

Fix: put `@vite(...)` (and anything else) *inside* `<x-filament-panels::page>...</x-filament-panels::page>`, never before it, so the view has one root element.

Debugging tip if `$wire.someProp` ever returns `() => {}` in the browser console: check that `document.querySelector('[wire\\:id]')` is actually an ancestor of the element using `$wire`, not a sibling — that's the signature of this bug.
