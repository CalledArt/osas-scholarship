{{--
    The full text of Republic Act No. 10931, transcribed from the signed copy
    published in the Official Gazette (20170803-RA-10931-RRD.pdf). Widely
    copied online versions drop words — Section 11 among them, reversing its
    meaning — so correct this against the signed copy, never a copy of it.
    Drawn like the other pages (asked 2026-10-25): the dashboards' banner, the
    title block, then what it means for the student beside the full text. From
    lg the page fits the screen and does not scroll: each panel fills the rest
    of it and scrolls inside itself. Only the frame around the Act changed; its
    words did not.
--}}
@php
    $student = Auth::user();
    $yearOfStudy = $student->yearOfStudy();
    $coveredYears = $student->freeTertiaryEducationYears();
    $isCovered = $student->hasFreeTertiaryEducation();
    // On leave of absence with covered years still left: paused, not ended.
    $isPaused = $student->isOnLeaveOfAbsence() && $student->isWithinFreeTertiaryEducationYears();
@endphp

<x-app-layout :title="__('Republic Act No. 10931')" :sheet="true">
    <div class="px-6 py-10 lg:flex lg:h-screen lg:flex-col lg:px-10 lg:py-6">
        <div class="mx-auto max-w-[100rem] lg:flex lg:min-h-0 lg:w-full lg:flex-col">
            <a href="{{ route('scholarships.index') }}" class="rounded font-medium lg:self-start text-graphite underline underline-offset-4 hover:text-ink focus:outline-none focus-visible:ring-2 focus-visible:ring-seal focus-visible:ring-offset-2">{{ __('Back to scholarships') }}</a>

            <div class="mt-4">
                <x-welcome-banner :heading="__('Republic Act No. 10931')" :vt-title="true">
                    {{ __('Universal Access to Quality Tertiary Education Act') }}
                </x-welcome-banner>
            </div>

            <x-title-block class="mt-6">
                <x-field :label="__('Approved')">{{ __('August 3, 2017') }}</x-field>
                <x-field :label="__('Congress')">{{ __('Seventeenth') }}</x-field>
                <x-field :label="__('Your standing')">{{ $isCovered ? __('Covered') : ($isPaused ? __('Paused') : __('Ended')) }}</x-field>
            </x-title-block>

            <div class="mt-6 grid gap-6 lg:min-h-0 lg:flex-1 lg:grid-cols-2 lg:grid-rows-[minmax(0,1fr)]">
                <section aria-labelledby="coverage-heading" @class([
                    'rounded-lg border p-5 sm:p-6 lg:flex lg:min-h-0 lg:flex-col',
                    'border-grant/30 bg-grant-wash' => $isCovered,
                    'border-rule bg-linen-sunk' => ! $isCovered,
                ])>
                    <div class="flex flex-wrap items-center justify-between gap-3">
                        <h2 id="coverage-heading" @class(['text-lead font-semibold', 'text-grant-deep' => $isCovered, 'text-ink' => ! $isCovered])>{{ __('What this means for you') }}</h2>
                        <x-badge :color="$isCovered ? 'green' : 'gray'">{{ $isCovered ? __('Active') : ($isPaused ? __('Paused') : __('Ended')) }}</x-badge>
                    </div>

                    {{-- From lg the heading stays put and only what follows it scrolls. --}}
                    <div class="relative lg:min-h-0 lg:flex-1 lg:overflow-y-auto">

                        <p class="mt-2  leading-relaxed text-graphite">
                            @if ($isCovered)
                                {{ __('Cavite State University is a state university, so under Section 4 you are exempt from paying tuition and other school fees for the units you enroll in toward your undergraduate degree.') }}
                            @elseif ($isPaused)
                                {{ __('Your free tuition under this Act is paused while you are on leave of absence (LOA). It resumes once you return to your studies and OSAS marks you active. Contact OSAS if your status is wrong.') }}
                            @else
                                {{ __('Your free tuition under this Act has ended. You are in year :year of study, past the :covered years it covers for your :program-year program under Section 6(a)(3). Contact OSAS if your student number or program length is wrong.', ['year' => $yearOfStudy, 'covered' => $coveredYears, 'program' => $student->program_years]) }}
                            @endif
                        </p>

                        <dl @class(['mt-4 divide-y border-y ', 'divide-grant/30 border-grant/30' => $isCovered, 'divide-rule border-rule' => ! $isCovered])>
                            @if ($isCovered && $yearOfStudy !== null && $coveredYears !== null)
                                <div class="grid gap-1 py-3 sm:grid-cols-3 sm:gap-4">
                                    <dt class="font-medium text-ink">{{ __('Your year of study') }}</dt>
                                    <dd class="text-graphite sm:col-span-2">
                                        <span class="font-semibold tabular-nums text-ink">{{ __('Year :year of :covered', ['year' => $yearOfStudy, 'covered' => $coveredYears]) }}</span>
                                        &mdash; {{ __('your :program-year program plus one year.', ['program' => $student->program_years]) }}
                                        @if ($yearOfStudy === $coveredYears)
                                            {{ __('This is your last covered school year.') }}
                                        @endif
                                    </dd>
                                </div>
                            @endif
                            <div class="grid gap-1 py-3 sm:grid-cols-3 sm:gap-4">
                                <dt class="font-medium text-ink">{{ __('What it covers') }}</dt>
                                <dd class="text-graphite sm:col-span-2">{{ __('Tuition, and school fees such as library, laboratory, computer, athletic, registration, and medical and dental fees.') }} <a href="#section-3" class="rounded-sheet text-seal underline underline-offset-4 hover:text-seal-deep focus:outline-none focus-visible:ring-2 focus-visible:ring-seal focus-visible:ring-offset-2">{{ __('Sections 3(h) and 4') }}</a></dd>
                            </div>
                            <div class="grid gap-1 py-3 sm:grid-cols-3 sm:gap-4">
                                <dt class="font-medium text-ink">{{ __('How long') }}</dt>
                                <dd class="text-graphite sm:col-span-2">{{ __('Up to one year beyond your program’s prescribed length — five years for a four-year program.') }} <a href="#section-6" class="rounded-sheet text-seal underline underline-offset-4 hover:text-seal-deep focus:outline-none focus-visible:ring-2 focus-visible:ring-seal focus-visible:ring-offset-2">{{ __('Section 6(a)(3)') }}</a></dd>
                            </div>
                            <div class="grid gap-1 py-3 sm:grid-cols-3 sm:gap-4">
                                <dt class="font-medium text-ink">{{ __('To keep it') }}</dt>
                                <dd class="text-graphite sm:col-span-2">{{ __('Meet the university’s admission and retention requirements.') }} <a href="#section-4" class="rounded-sheet text-seal underline underline-offset-4 hover:text-seal-deep focus:outline-none focus-visible:ring-2 focus-visible:ring-seal focus-visible:ring-offset-2">{{ __('Sections 4 and 6(a)(2)') }}</a></dd>
                            </div>
                            <div class="grid gap-1 py-3 sm:grid-cols-3 sm:gap-4">
                                <dt class="font-medium text-ink">{{ __('Not eligible') }}</dt>
                                <dd class="text-graphite sm:col-span-2">{{ __('Students who already hold a bachelor’s or comparable undergraduate degree.') }} <a href="#section-6" class="rounded-sheet text-seal underline underline-offset-4 hover:text-seal-deep focus:outline-none focus-visible:ring-2 focus-visible:ring-seal focus-visible:ring-offset-2">{{ __('Section 6(a)(1)') }}</a></dd>
                            </div>
                        </dl>

                        @if ($isCovered)
                            <p class="mt-4  text-graphite">
                                {{ __('This benefit is granted by law, so it does not count toward your limit of :limit scholarships.', ['limit' => \App\Models\User::SCHOLARSHIP_LIMIT]) }}
                            </p>
                        @endif
                    </div>
                </section>

                <article aria-labelledby="text-heading" class="overflow-hidden rounded-lg border border-rule bg-white lg:flex lg:min-h-0 lg:flex-col">
                    <h2 id="text-heading" class="flex items-center gap-2.5 px-6 py-4 text-lead font-semibold text-ink">
                        <svg class="h-5 w-5 shrink-0 text-seal" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 6.5C10.5 5 8 4.5 4 4.5v14c4 0 6.5.5 8 2m0-14c1.5-1.5 4-2 8-2v14c-4 0-6.5.5-8 2m0-14v14" />
                        </svg>
                        {{ __('Full text of the Act') }}
                    </h2>

                    {{-- Relative and scroll-padded, so a section link from the panel beside it lands with its heading in view. --}}
                    <div class="relative scroll-pt-4 border-t border-rule px-6 pb-6 leading-relaxed text-graphite [&_h3]:mt-8 [&_h3]:font-semibold [&_h3]:text-ink [&_p]:mt-3 [&_ol]:mt-3 [&_ol]:space-y-2 [&_ol]:pl-6 [&_ol_ol]:mt-2 lg:min-h-0 lg:flex-1 lg:overflow-y-auto">
                        <p class="!mt-5 italic leading-relaxed text-slate">
                            An Act promoting universal access to quality tertiary education by providing for free tuition and other school fees in state universities and colleges, local universities and colleges and state-run technical-vocational institutions, establishing the tertiary education subsidy and student loan program, strengthening the Unified Student Financial Assistance System for Tertiary Education, and appropriating funds therefor
                        </p>

                        <p><em>Be it enacted by the Senate and House of Representatives of the Philippines in Congress assembled:</em></p>

                        <section id="section-1" class="scroll-mt-6">
                            <h3>Section 1. <em class="font-normal">Short Title.</em></h3>
                            <p>This Act shall be known as the “Universal Access to Quality Tertiary Education Act”.</p>
                        </section>

                        <section id="section-2" class="scroll-mt-6">
                            <h3>Section 2. <em class="font-normal">Declaration of Policy.</em></h3>
                            <p>It is hereby declared that quality education is an inalienable right of all Filipinos and it is the policy of the State to protect and promote the rights of all students to quality education at all levels. Therefore, the State shall take appropriate steps to make such education accessible to all.</p>
                            <p>Likewise, the State hereby recognizes the complementary roles of public and private higher education institutions and technical-vocational institutions in the educational system and the invaluable contribution that the private tertiary schools have made and will make to education. For these intents, the State shall:</p>
                            <ol>
                                <li>(a) Provide adequate funding and such other mechanisms to increase the participation rate among all socioeconomic classes in tertiary education;</li>
                                <li>(b) Provide all Filipinos with equal opportunity to quality tertiary education in both the private and public educational institutions;</li>
                                <li>(c) Give priority to students who are academically able and who come from poor families;</li>
                                <li>(d) Ensure the optimized utilization of government resources in education;</li>
                                <li>(e) Provide adequate guidance and incentives in channelling young Filipinos in their career choices and towards the proper development and utilization of human resources; and</li>
                                <li>(f) Recognize the complementary roles of public and private institutions in tertiary educational system.</li>
                            </ol>
                        </section>

                        <section id="section-3" class="scroll-mt-6">
                            <h3>Section 3. <em class="font-normal">Definition of Terms.</em></h3>
                            <p>As used in this Act:</p>
                            <ol>
                                <li>(a) <em>Cost of Tertiary Education</em> refers to (1) tuition and other school fees, (2) educational expenses and (3) the cost of living allowance;</li>
                                <li>(b) <em>Graduate courses</em> refer to higher education programs leading to a certificate, diploma, master’s or doctorate degrees, as may be authorized and recognized by the Commission on Higher Education (CHED);</li>
                                <li>(c) <em>Higher education</em> refers to the stage of formal education, or its equivalent, requiring completion of secondary education and covering programs of study leading to bachelor and advanced degrees, including associate degrees;</li>
                                <li>(d) <em>Higher Education Institution (HEI)</em> refers to an education institution authorized and recognized by the CHED to offer bachelor’s degree or graduate courses;</li>
                                <li>(e) <em>Local universities and colleges (LUCs)</em> refer to CHED-accredited public HEIs established by local government units (LGUs) through an enabling ordinance, financially supported by the LGU concerned, and compliant with the policies, standards and guidelines of the CHED;</li>
                                <li>(f) <em>National Household Targeting System for Poverty Reduction (NHTS-PR) or Listahanan 2.0</em> refers to the information management of the Department of Social Welfare and Development (DSWD) that identifies who and where the poor are in the country. The system makes available to national government agencies and other social protection stakeholders a database of poor families as reference in identifying potential beneficiaries of social protection programs;</li>
                                <li>(g) <em>Other education-related expenses</em> refer to expenses related to the education of a student such as books, school supplies, uniforms, reproduction of materials, electronic devices necessary for education and other fees such as for practical teaching devices, student publication, yearbook, insurance, and student trust funds;</li>
                                <li>(h) <em>Other school fees</em> refer to fees charged by HEIs and technical-vocational institutions which cover other necessary costs supportive of instruction, specifically the following: library fees, computer fees, laboratory fees, school ID fees, athletic fees, admission fees, development fees, guidance fees, handbook fees, entrance fees, registration fees, medical and dental fees, cultural fees and other similar or related fees;</li>
                                <li>(i) <em>Private higher education institution</em> refers to a HEI not owned and controlled by the government or its instrumentalities;</li>
                                <li>(j) <em>Private technical-vocational institution</em> refers to post-secondary technical-vocational institution run by the private sector offering programs registered with the Technical Education and Skills Development Authority (TESDA);</li>
                                <li>(k) <em>Qualified student</em> refers to any student who possesses all the qualifications under Sections 4 and 5 and none of the disqualifications under Section 6 hereof;</li>
                                <li>(l) <em>State-run technical-vocational institutions</em> refer to technical-vocational institutions operated by the TESDA or LGUs: <em>Provided</em>, That in the latter case, the same should be accredited by the TESDA;</li>
                                <li>(m) <em>State universities and colleges (SUCs)</em> refer to public HEIs established by national laws which are financed and maintained by the national government and are governed by their respective independent boards of trustees or regents;</li>
                                <li>(n) <em>Student loan program for tertiary education</em> refers to a loan program established under Section 8 of this Act;</li>
                                <li>(o) <em>Technical-Vocational Education and Training (TVET)</em> refers to the education process designed at post-secondary and lower tertiary levels, officially recognized as nondegree programs aimed at preparing technicians, paraprofessionals and other categories of middle-level workers by providing them with a broad range of general education, theoretical, scientific and technological studies, and related job skills training;</li>
                                <li>(p) <em>Technical-Vocational Institutions (TVIs)</em> refer to learning institutions offering post-secondary TVET;</li>
                                <li>(q) <em>Tertiary education</em> refers to the stage of education following the secondary cycle which covers post-secondary nondegree diploma, TVET, and higher education programs, including graduate education;</li>
                                <li>(r) <em>Tertiary education subsidy (TES)</em> refers to a subsidy established under Section 7 of this Act;</li>
                                <li>(s) <em>Tuition fees</em> refer to fees or school charges for the subjects or course enrolled in by a tertiary education student;</li>
                                <li>(t) <em>Undergraduate courses</em> refer to any program leading to a degree as may be authorized and recognized by the CHED; and</li>
                                <li>(u) <em>Unified Student Financial Assistance System for Tertiary Education (UniFAST)</em> refers to the harmonized, state-run and administered system of higher education and technical-vocational scholarships, grants-in-aid, student loans, and other modalities of student financial assistance program under Republic Act No. 10687.</li>
                            </ol>
                        </section>

                        <section id="section-4" class="scroll-mt-6">
                            <h3>Section 4. <em class="font-normal">Free Higher Education in SUCs and LUCs.</em></h3>
                            <p>All Filipino students who are either currently enrolled at the time of the effectivity of this Act, or shall enroll at any time thereafter, in courses in pursuance of a bachelor’s degree, certificate degree, or any comparable undergraduate degree in any SUC and LUC shall be exempt from paying tuition and other school fees for units enrolled in: <em>Provided</em>, That they pass the entrance examination and other admission and retention requirements of the SUCs and LUCs: <em>Provided, further</em>, That all SUCs and LUCs shall create a mechanism to enable students with the financial capacity to pay for their education in the SUC and LUC to voluntarily opt out of the tuition and other school fees subsidy or make a contribution to the school. SUCs and LUCs must report the tuition payments and contributions collected from these students to the CHED: <em>Provided, finally</em>, That the amount required to implement the free tuition and other school fees in SUCs and LUCs shall be determined by the respective governing boards of SUCs and LUCs based on the projected number of enrollees for each academic year, which shall be the primary factor in computing the annual proposed budget of SUCs and, in the case of LUCs, the CHED for such purpose. This shall in turn serve as the baseline during the preparation of the annual National Expenditure Program (NEP) by the Department of Budget and Management (DBM).</p>
                        </section>

                        <section id="section-5" class="scroll-mt-6">
                            <h3>Section 5. <em class="font-normal">Free TVET in Post-Secondary TVIs.</em></h3>
                            <p>All Filipino students who are currently enrolled at the time of the effectivity of this Act, or shall enroll at any time thereafter in any post-secondary TVET leading to nondegree certificate or diploma programs offered by any state-run TVI under the TESDA shall be exempt from paying tuition and other school fees: <em>Provided</em>, That all state-run TVIs shall create a mechanism to enable students with the financial capacity to pay for their education in the TVI to voluntarily opt out of the tuition and other school fees subsidy or make a contribution to the TVI. TVIs must report the tuition payments and contributions collected from these students.</p>
                            <p>The amount required to implement the free tuition and other school fees in state-run TVIs shall be determined by the governing board of the TESDA based on the projected number of enrollees for each course, which shall be the primary factor in computing for the annual proposed budget of the TESDA for such purpose. This shall in turn serve as the baseline during the preparation of the annual NEP by the DBM.</p>
                        </section>

                        <section id="section-6" class="scroll-mt-6">
                            <h3>Section 6. <em class="font-normal">Exceptions to Free Tertiary Education.</em></h3>
                            <p>The following students are ineligible to avail of the free tertiary education:</p>
                            <ol>
                                <li>
                                    (a) In SUCs and LUCs:
                                    <ol>
                                        <li>(1) Students who have already attained a bachelor’s degree or comparable undergraduate degree from any HEI, whether public or private;</li>
                                        <li>(2) Students who fail to comply with the admission and retention policies of the SUC or LUC;</li>
                                        <li>(3) Students who fail to complete their bachelor’s degree or comparable undergraduate degree within a year after the period prescribed in their program; and</li>
                                    </ol>
                                </li>
                                <li>
                                    (b) In State-Run TVIs:
                                    <ol>
                                        <li>(1) Students who have obtained a bachelor’s degree, as well as those who have received a certificate or diploma for a technical-vocational course equivalent to at least National Certificate III and above;</li>
                                        <li>(2) Students who fail in any course enrolled in during the course of the program.</li>
                                    </ol>
                                </li>
                            </ol>
                            <p>Students ineligible to avail of the free tertiary education shall be charged the tuition and other school fees, as determined by the respective boards of the SUCs and LUCs, and in the case of the state-run TVIs, to be determined by the TESDA.</p>
                        </section>

                        <section id="section-7" class="scroll-mt-6">
                            <h3>Section 7. <em class="font-normal">TES for Filipino Students.</em></h3>
                            <p>To support the cost of tertiary education or any part or portion thereof, a TES is hereby established for all Filipino students who shall enroll in undergraduate post-secondary programs of SUCs, LUCs, private HEIs and all TVIs. The TES shall be administered by the UniFAST Board and the amount necessary to fund the TES shall be included in the budgets of the CHED and the TESDA: <em>Provided</em>, That prioritization shall be given to students in the following order: (a) students who are part of households included in the <em>Listahanan</em> 2.0, ranked according to the estimated per capita household income; and (b) students not part of the <em>Listahanan</em> 2.0, ranked according to estimated per capita household income based on submitted documentation of proof of income to be determined by the UniFAST Board: <em>Provided, further</em>, That such prioritization shall not apply to Filipino students in cities and municipalities with no existing SUC or LUC campus.</p>
                            <p>The TES may, among others, and to support the cost of tertiary education or any part or portion thereof, cover the following:</p>
                            <ol>
                                <li>(a) Tuition and other school fees in private HEIs, and private or LGU-operated TVIs, which shall be equivalent to the tuition and other school fees of the nearest SUC or state-run TVI in their respective areas;</li>
                                <li>(b) An allowance for books, supplies, transportation, and miscellaneous personal expenses, including a reasonable allowance for the documented rental or purchase of a personal computer or laptop and other education-related expenses;</li>
                                <li>(c) An allowance for room and board costs incurred by the student;</li>
                                <li>(d) For a student with a disability, an allowance for expenses related to the student’s disability, including special services, personal assistance, transportation, equipment, and supplies that are reasonably incurred; and</li>
                                <li>(e) For a student in a program requiring professional license or certification, the one (1)-time cost of obtaining the first professional credentials or qualifications, which may include the following: application fees, notarial fees, review classes fees, insurance premium fees and documentation fees: <em>Provided</em>, That the amount of subsidy shall be based on the guidelines set forth by the UniFAST Board and on the annual budgetary appropriation for this purpose.</li>
                            </ol>
                        </section>

                        <section id="section-8" class="scroll-mt-6">
                            <h3>Section 8. <em class="font-normal">Student Loan Program for Tertiary Education.</em></h3>
                            <p>To support further the cost of tertiary education or any part or portion thereof, a student loan program for tertiary education is hereby likewise established for all Filipino students who shall enroll in a SUC, LUC, private HEI and TVET program in all TVIs registered under the TESDA. The loan program shall be administered by the UniFAST Board and the amount necessary to fund the program shall be included in the budgets of the CHED and the TESDA: <em>Provided</em>, That the UniFAST may offer short-term or long-term loans: <em>Provided, further</em>, That those who availed of the loan during their undergraduate degree may still avail of another cycle of student loan for their pursuit of graduate studies, including medicine and law after they have fully paid the previously availed loan: <em>Provided, furthermore</em>, That those who did not avail of the loan program during their undergraduate studies may avail of it to pursue graduate studies including medicine and law: <em>Provided, finally</em>, That those who did not avail of the loan program during their undergraduate studies may avail of the loan program for their review expenses for licensure examinations administered by the Professional Regulation Commission (PRC).</p>
                            <p>Repayment shall be effected by incorporating a portion of the loan amount or a percentage thereof in the employee’s monthly Social Security System (SSS) or Government Service Insurance System (GSIS) contribution, as the case may be, based on a reasonable schedule of repayment and interest rates, as may be formulated by the UniFAST Board.</p>
                            <p>Payment of the loan amount will commence once the beneficiary secures any gainful employment with compensation, remuneration or earnings that reaches the Compulsory Repayment Threshold (CRT). For purposes of this Act, the CRT shall be set and reviewed by the UniFAST Board, and adjusted when necessary.</p>
                            <p>The UniFAST Board, in consultation with relevant agencies, shall formulate loan repayment guidelines for loan beneficiaries whose earnings are not covered by the GSIS or the SSS programs, including those of overseas Filipino workers (OFWs), emigrants, and self-employed persons and professionals (SEPs).</p>
                        </section>

                        <section id="section-9" class="scroll-mt-6">
                            <h3>Section 9. <em class="font-normal">Requirements for SUCs and LUCs.</em></h3>
                            <p>SUCs and LUCs are hereby mandated to:</p>
                            <ol>
                                <li>(a) Establish a learner information system in accordance with the guidelines to be developed by the CHED in order to facilitate the tracking of students and their performance;</li>
                                <li>(b) Submit relevant information as determined by the CHED on school quality and performance; and</li>
                                <li>(c) Formulate and submit to the CHED and to the Joint Congressional Oversight Committee on Universal Access to Tertiary Education created under Section 17 of this Act, a detailed SUC development plan updated every ten (10) years which shall include plans for facilities and infrastructure development and expansion.</li>
                            </ol>
                        </section>

                        <section id="section-10" class="scroll-mt-6">
                            <h3>Section 10. <em class="font-normal">Quality Standards for SUC and LUC Budgets, TES and Student Loan Programs.</em></h3>
                            <p>The CHED and the TESDA shall ensure quality standards in the review and consequent endorsement of the budget of the SUCs, LUCs and state-run TVIs, respectively. The detailed design of the TES and student loan programs shall also be subject to similar quality indicators defined by the UniFAST Board.</p>
                        </section>

                        <section id="section-11" class="scroll-mt-6">
                            <h3>Section 11. <em class="font-normal">Prohibited Act.</em></h3>
                            <p>Upon effectivity of this Act, it shall be unlawful for any person, SUC, LUC and state-run TVI to collect tuition and other school fees from qualified students: <em>Provided</em>, That this section shall not apply to collections from students who voluntarily opt out of the tuition and other school fees subsidy or make a contribution to the school.</p>
                        </section>

                        <section id="section-12" class="scroll-mt-6">
                            <h3>Section 12. <em class="font-normal">Penalties.</em></h3>
                            <p>A violation of the prohibited act under Section 11 of this Act shall be meted a penalty of imprisonment of not less than six (6) months but not more than one (1) year or a fine of not less than Twenty thousand pesos (₱20,000.00) but not more than One hundred thousand pesos (₱100,000.00), or both, at the discretion of the court. In case of a university, college or any other juridical entity, the penalty shall be imposed on the president, treasurer or the officer or person responsible for the violation.</p>
                        </section>

                        <section id="section-13" class="scroll-mt-6">
                            <h3>Section 13. <em class="font-normal">Expansion of the UniFAST Board.</em></h3>
                            <p>The UniFAST Board shall be expanded to include the following:</p>
                            <ol>
                                <li>(a) President of the Philippine Association of State Universities and Colleges as Member;</li>
                                <li>(b) Chairman of the Coordinating Council of Private Educational Associations as Member;</li>
                                <li>(c) President of the Association of Local Colleges and Universities as Member;</li>
                                <li>(d) President of the GSIS as a nonvoting Member; and</li>
                                <li>(e) President of the SSS as a nonvoting Member.</li>
                            </ol>
                            <p>The UniFAST Board is authorized to establish an enhanced organizational structure, staff development and incentives and such other administrative measures needed for the efficient discharge of tasks and commensurate to the level and scope of its responsibilities. It may tap the expertise and management services of eligible service providers subject to the appropriate guidelines promulgated by the UniFAST Board.</p>
                        </section>

                        <section id="section-14" class="scroll-mt-6">
                            <h3>Section 14. <em class="font-normal">Reporting Requirements.</em></h3>
                            <p>All SUCs, LUCs and state-run TVIs shall submit to the CHED and the TESDA, respectively, within five (5) days after the last day of late registration for each semester, a report detailing the names of students eligible for the free tuition and other school fees in their institutions.</p>
                        </section>

                        <section id="section-15" class="scroll-mt-6">
                            <h3>Section 15. <em class="font-normal">Appropriations.</em></h3>
                            <p>The amounts necessary to carry out the provisions of this Act, specifically Sections 4, 5, 7 and 8 shall be included in the annual General Appropriations Act (GAA) and shall be appropriated under SUCs, CHED and TESDA in accordance with the provisions of this Act: <em>Provided</em>, That an amount equivalent to not more than three percent (3%) of the TES and student loan program for tertiary education provided under this Act may be used as administrative cost under the UniFAST.</p>
                        </section>

                        <section id="section-16" class="scroll-mt-6">
                            <h3>Section 16. <em class="font-normal">Other Sources of Funds.</em></h3>
                            <p>The national government is hereby authorized to prioritize funding this measure in negotiating and utilizing long-term deeply concessional official development assistance (ODA). Other sources of funds such as grants, donations, collections, and other forms of assistance from local and foreign donors or other public or private entities, and other private domestic and international sources may be tapped and facilitated by the UniFAST Board to support the programs under this Act, subject to the regular auditing guidelines and procedures: <em>Provided</em>, That in case of donations from foreign sources, acceptance thereof shall be subject to existing government rules and regulations.</p>
                        </section>

                        <section id="section-17" class="scroll-mt-6">
                            <h3>Section 17. <em class="font-normal">Joint Congressional Oversight Committee on Universal Access to Tertiary Education.</em></h3>
                            <p>There is hereby created a Joint Congressional Oversight Committee to oversee, monitor and evaluate the implementation of this Act.</p>
                            <p>The Oversight Committee shall be composed of five (5) members each from the Senate and from the House of Representatives, and shall include the following: Chairperson of the Senate Committee on Education, Arts and Culture; Chairperson of the House Committee on Higher and Technical Education; Chairperson of the Senate Committee on Finance; Chairperson of the House Committee on Appropriations; and three (3) members each to be chosen from the membership of the Senate Committee on Education, Arts and Culture and the House Committee on Higher and Technical Education by the Senate President and the House Speaker, respectively, with at least one (1) member each from the minority in the House of Representatives and in the Senate. Funding for the expenses of the Committee shall be taken from the appropriations of both the Senate and the House of Representatives.</p>
                        </section>

                        <section id="section-18" class="scroll-mt-6">
                            <h3>Section 18. <em class="font-normal">Implementing Rules and Regulations.</em></h3>
                            <p>Within sixty (60) days from the effectivity of this Act, the UniFAST Board, in consultation with the CHED, the TESDA, and other relevant stakeholders in higher and technical education, shall promulgate the implementing rules and regulations necessary to ensure the efficient and effective implementation of this Act: <em>Provided</em>, That the failure of the Board to promulgate the said rules and regulations shall not prevent or delay the effectivity and implementation of this Act in accordance with Section 21 hereof.</p>
                        </section>

                        <section id="section-19" class="scroll-mt-6">
                            <h3>Section 19. <em class="font-normal">Separability Clause.</em></h3>
                            <p>Should any part of this Act be declared unconstitutional or invalid, the other parts or provisions hereof not affected thereby shall continue to be in full force and effect.</p>
                        </section>

                        <section id="section-20" class="scroll-mt-6">
                            <h3>Section 20. <em class="font-normal">Repealing Clause.</em></h3>
                            <p>All laws, executive orders, presidential decrees, implementing rules and regulations or parts thereof inconsistent with the provisions of this Act are hereby repealed or modified accordingly.</p>
                        </section>

                        <section id="section-21" class="scroll-mt-6">
                            <h3>Section 21. <em class="font-normal">Effectivity.</em></h3>
                            <p>This Act shall take effect fifteen (15) days after its publication in the <em>Official Gazette</em> or in a newspaper of general circulation.</p>
                        </section>

                        <footer class="mt-10 border-t border-rule pt-6  text-slate">
                            <p class="!mt-0">This Act is a consolidation of Senate Bill No. 1304 and House Bill No. 5633, finally passed by the Senate and the House of Representatives on May 30, 2017 and May 29, 2017, respectively.</p>
                            <p>Approved August 3, 2017, by <span class="font-medium text-ink">Rodrigo Roa Duterte</span>, President of the Philippines.</p>
                            <p>{{ __('Text as published in the Official Gazette of the Republic of the Philippines.') }}</p>
                        </footer>
                    </div>
                </article>
            </div>
        </div>
    </div>
</x-app-layout>
