---
paths:
  - 'resources/views/tutorial/**'
---

# Tutorial

## The practice tour must never reach real records
The tutorial page (/tutorial) acts out the flow on sample screens held in Alpine state. Its stage has no <form>, no name= inputs and no links to real records; the only real request is the tutorial.complete POST. TutorialTest::test_the_practice_tour_can_only_mark_itself_seen enforces this by listing every form action on the page — keep it passing rather than loosening it.

## Toggle tutorial stage elements with :class hidden, not x-show
Alpine's x-show reveals an element on the next requestAnimationFrame, but the tour guide measures its spotlight target synchronously after each stage action. With x-show the spotlight misses newly shown screens and rows (or comes out too short). Use :class="{ 'hidden': ... }" for anything the stage shows or hides while a step plays.
