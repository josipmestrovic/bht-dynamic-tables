/**
 * BHT Dynamic Tables — departure accordion controller.
 *
 * Click (or Enter / Space — handled natively by <button>) on a
 * `.departure-toggle` toggles the linked `.departure-body`.
 *
 * The collapsed state lives on a `.is-collapsed` class — NOT the native
 * `hidden` attribute — so we can animate `max-height` + `opacity`
 * smoothly. Initial collapsed rows ship with the class already applied
 * by the PHP renderer, so they render at height 0 with no flash.
 *
 * Pure vanilla, no dependencies. IIFE-scoped.
 */
(function () {
    'use strict';

    var TRANSITION_MS = 220;

    /**
     * Expand a collapsed body.
     * @param {HTMLElement} body
     */
    function expand(body) {
        // Measure the natural height while still collapsed (max-height: 0
        // is fine — scrollHeight ignores it).
        var target = body.scrollHeight;

        // Set an explicit starting height so the transition has a from-value.
        body.style.maxHeight = '0px';
        body.classList.remove('is-collapsed');

        // Next frame: animate to the measured height.
        requestAnimationFrame(function () {
            body.style.maxHeight = target + 'px';
        });

        // After the animation, clear inline max-height so the body can
        // grow freely if its content changes later.
        window.setTimeout(function () {
            if (!body.classList.contains('is-collapsed')) {
                body.style.maxHeight = '';
            }
        }, TRANSITION_MS + 30);
    }

    /**
     * Collapse an open body.
     * @param {HTMLElement} body
     */
    function collapse(body) {
        // Lock the current height so the transition has a from-value.
        body.style.maxHeight = body.scrollHeight + 'px';

        // Force a reflow so the browser registers the explicit height
        // before we change it.
        // eslint-disable-next-line no-unused-expressions
        body.offsetHeight;

        requestAnimationFrame(function () {
            body.classList.add('is-collapsed');
            body.style.maxHeight = '0px';
        });
    }

    document.addEventListener('click', function (event) {
        var toggle = event.target.closest('.departure-toggle');
        if (!toggle) {
            return;
        }
        var bodyId = toggle.getAttribute('aria-controls');
        var body   = bodyId && document.getElementById(bodyId);
        if (!body) {
            return;
        }
        var block      = toggle.closest('.departure-block');
        var isExpanded = toggle.getAttribute('aria-expanded') === 'true';

        toggle.setAttribute('aria-expanded', isExpanded ? 'false' : 'true');
        if (block) {
            block.classList.toggle('is-open', !isExpanded);
        }

        if (isExpanded) {
            collapse(body);
        } else {
            expand(body);
        }
    });
})();
