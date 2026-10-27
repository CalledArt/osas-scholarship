import defaultTheme from 'tailwindcss/defaultTheme';
import forms from '@tailwindcss/forms';

/**
 * Lot and Claim — see DESIGN.md.
 *
 * A scholarship is a surveyed parcel: a bounded capacity drawn as countable
 * slots, a closing date in a title block, and an application staked on one of
 * them. The palette is the university's own — Seal Green for the shell and for
 * what commits, Citation Gold for what is yours or still waiting — and every
 * value below has one job. Nothing here is decorative.
 */
/** @type {import('tailwindcss').Config} */
export default {
    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './storage/framework/views/*.php',
        './resources/views/**/*.blade.php',
    ],

    theme: {
        extend: {
            fontFamily: {
                // Body, headings and controls. Tabular figures are switched on
                // globally in app.css, because every number here is compared.
                sans: ['Archivo', ...defaultTheme.fontFamily.sans],
                // Plan annotations only: column headers, field labels, status
                // marks. Always uppercase, always tracked. Never a sentence.
                annot: ['"Archivo Narrow"', 'Archivo', ...defaultTheme.fontFamily.sans],
                // Values a reader must transcribe exactly: student numbers,
                // reference numbers, literal CSV headers. Not a costume.
                mono: ['"Spline Sans Mono"', ...defaultTheme.fontFamily.mono],
            },

            // Four sizes for the whole authenticated application, plus `display`,
            // which is reserved to the unauthenticated front door and appears
            // nowhere inside the app. Rank is carried by weight, case, reversal
            // and rule — never by reaching for a fifth interior size.
            fontSize: {
                annot: ['0.75rem', { lineHeight: '1rem', letterSpacing: '0.08em' }],
                body: ['0.9375rem', { lineHeight: '1.375rem' }],
                lead: ['1.125rem', { lineHeight: '1.625rem' }],
                figure: ['2rem', { lineHeight: '1', letterSpacing: '-0.02em' }],
                display: ['3.25rem', { lineHeight: '1.03', letterSpacing: '-0.025em' }],
            },

            colors: {
                // The sheet. Warm off-white: a plan is printed on stock, never
                // on pure white, and pure white glares on a phone outdoors.
                linen: {
                    DEFAULT: '#FBFAF7',
                    sunk: '#F4F2ED',
                },
                // The shell: rail and header band, in Cavite State University's
                // own green. A committed field, not a grey — and the seal sits
                // in its own colour rather than on a foreign one.
                seal: {
                    DEFAULT: '#0B6E4F',
                    deep: '#064532',
                    // Secondary text *on* the shell, tinted from the green
                    // itself rather than greyed — 4.6:1 on the rail.
                    tint: '#BFE5D6',
                    line: '#1C9A6F',
                    // The admin dashboard's welcome banner only, kept to the
                    // mock OSAS supplied (decided 2026-10-24).
                    wash: '#EEF7F3',
                },
                // Ink. Only these three carry running text on linen.
                ink: '#10213A',
                graphite: '#3D4654',
                slate: '#5A6472',
                // Hairlines. Rule separates rows; Rule Strong draws the strokes
                // that carry meaning, dark enough to clear 3:1 on every paper tone.
                rule: {
                    DEFAULT: '#D8D4CB',
                    strong: '#8C8678',
                },
                // Citation Gold: this record is YOURS, or it waits on a human.
                // Never a text colour (1.5:1 on linen).
                gold: {
                    DEFAULT: '#FFC72C',
                    deep: '#B87F00',
                    wash: '#FDF3DA',
                },
                // Granted and settled. A pale wash with dark green ink, far
                // enough in value from the shell that the two never read alike.
                grant: {
                    DEFAULT: '#15803D',
                    deep: '#116430',
                    wash: '#DCFCE7',
                },
                // The rejection stamp, and destructive controls. Nothing else.
                oxide: {
                    DEFAULT: '#A8341F',
                    deep: '#862715',
                    wash: '#F7E4E0',
                },
            },

            borderRadius: {
                // Controls get the slightest softening so they read as handles.
                sheet: '2px',
                // The plan geometry — parcels, lots, title blocks, tables — keeps
                // true corners. A survey drawing has no rounded parcels.
            },

            boxShadow: {
                // Tinted from ink, never black, and always with a real offset
                // and blur. A zero-offset halo is decoration, not depth.
                seat: '0 1px 2px 0 rgb(16 33 58 / 0.06)',
                lift: '0 10px 24px -8px rgb(16 33 58 / 0.22)',
                sheet: '0 20px 40px -12px rgb(16 33 58 / 0.28)',
            },

            spacing: {
                rail: '15rem', // 240px — the shell's fixed column.
            },

            maxWidth: {
                // Chosen by the reader's task, not by content volume.
                sheet: '64rem', // lists read top to bottom
                plan: '80rem', // wide administrative comparison surfaces
                leaf: '42rem', // a single record, and prose at 65–75ch
                stub: '22rem', // auth forms
            },

            transitionTimingFunction: {
                // Exponential ease-out, from an already-visible default.
                sheet: 'cubic-bezier(0.16, 1, 0.3, 1)',
            },
        },
    },

    plugins: [forms],
};
