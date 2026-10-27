/**
 * The application shell's navigation state.
 *
 * Below 1024px the rail becomes a slide-over, which has to behave like a real
 * dialog: focus moves into it on open, cannot leave it while it is open, and
 * returns to whatever opened it on close. The isolation is carried by the
 * `inert` property on the page behind it rather than by a hand-written tab
 * cycle — an inert subtree is removed from the tab order and from the
 * accessibility tree, so the browser does the trapping and there is no keyboard
 * path out of the panel.
 *
 * Alpine's Focus plugin would also do this, but it is not a dependency of this
 * project and adding one is not this change's business.
 */
export default () => ({
    navOpen: false,

    /** The element that opened the panel, so focus can be handed back to it. */
    navOpener: null,

    /** Whether the practice tour opened the panel to point into it. */
    navForTour: false,

    openNav(event) {
        this.navOpener = event?.currentTarget ?? null;
        this.navForTour = false;
        this.navOpen = true;

        // After Alpine has shown the panel, put the caret on its first control.
        this.$nextTick(() => {
            this.$refs.navPanel
                ?.querySelector('a[href], button:not([disabled]), [tabindex]:not([tabindex="-1"])')
                ?.focus();
        });
    },

    /**
     * Open the panel for the practice tour to point at a menu item. The tour's
     * guide lives in the page behind, so that page stays live and focus stays
     * on the guide: made inert, it would leave the guide's own buttons out of
     * reach while it points, with no way on through the tour.
     */
    openTourNav() {
        this.navOpener = null;
        this.navForTour = true;
        this.navOpen = true;
    },

    closeNav() {
        if (! this.navOpen) {
            return;
        }

        this.navOpen = false;
        this.navForTour = false;

        // Returning focus to the trigger is what stops a keyboard user being
        // dropped at the top of the document after dismissing the panel.
        const opener = this.navOpener;
        this.navOpener = null;

        this.$nextTick(() => opener?.focus());
    },

    /**
     * Mark a subtree inert while the panel is open. Bound with x-effect and set
     * as a DOM property, so it does not depend on Alpine's boolean-attribute
     * list containing `inert`.
     */
    isolate(element) {
        element.inert = this.navOpen && ! this.navForTour;
    },
});
