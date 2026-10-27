<?php

namespace App\Support;

use App\Models\Scholarship;
use App\Models\ScholarshipApplication;
use App\Models\User;
use Carbon\CarbonInterface;
use Illuminate\Support\Collection;
use RuntimeException;
use ZipArchive;

/**
 * A scholarship's fund allocation as an Excel workbook (.xlsx): the CvSU logo
 * and masthead, then one row per student who passed with their equal share
 * as the Amount Received and a blank Signature column each student signs on
 * the printed sheet.
 *
 * Written straight as Office Open XML with ZipArchive, so it needs no
 * spreadsheet library (and no GD: the logo goes in as the PNG it is). The
 * sheet prints A4 landscape, one page wide, with the column headings
 * repeated on every page.
 */
class FundAllocationSheet
{
    public const CONTENT_TYPE = 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet';

    private const SHEET_NAME = 'Fund Allocation';

    private const HEADING_ROW = 6;

    private const LAST_COLUMN = 'H';

    /**
     * How tall the logo stands, in points; the four masthead rows beside it run about 64pt.
     */
    private const LOGO_HEIGHT = 60;

    private const TABLE_ROW_HEIGHT = 30;

    private const STYLE_PLAIN = 0;

    private const STYLE_BOLD_CENTERED = 1;

    private const STYLE_CENTERED = 2;

    private const STYLE_TITLE = 3;

    private const STYLE_HEADING = 4;

    private const STYLE_CELL = 5;

    private const STYLE_CELL_CENTERED = 6;

    private const STYLE_CELL_AMOUNT = 7;

    private const STYLE_TOTAL_LABEL = 8;

    private const STYLE_TOTAL_AMOUNT = 9;

    private const STYLE_SIGNATURE_LINE = 10;

    /**
     * The cell ranges merged so far, such as "A1:H1".
     *
     * @var list<string>
     */
    private array $merges = [];

    /**
     * @param  Collection<int, ScholarshipApplication>  $passedApplications  in the order they print, with their users loaded
     */
    public function __construct(
        private readonly Scholarship $scholarship,
        private readonly Collection $passedApplications,
        private readonly User $preparedBy,
        private readonly CarbonInterface $generatedAt,
    ) {}

    /**
     * The workbook as the contents of an .xlsx file.
     */
    public function toXlsx(): string
    {
        $path = tempnam(sys_get_temp_dir(), 'fund-allocation');
        $zip = new ZipArchive;

        try {
            if ($zip->open($path, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
                throw new RuntimeException('The fund allocation workbook could not be created.');
            }

            foreach ($this->parts() as $name => $xml) {
                $zip->addFromString($name, $xml);
            }

            $zip->close();

            return file_get_contents($path);
        } finally {
            unlink($path);
        }
    }

    /**
     * The workbook's files, by their path inside the package.
     *
     * @return array<string, string>
     */
    private function parts(): array
    {
        return [
            '[Content_Types].xml' => $this->xml(<<<'XML'
                <Types xmlns="http://schemas.openxmlformats.org/package/2006/content-types">
                <Default Extension="rels" ContentType="application/vnd.openxmlformats-package.relationships+xml"/>
                <Default Extension="xml" ContentType="application/xml"/>
                <Default Extension="png" ContentType="image/png"/>
                <Override PartName="/xl/workbook.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.sheet.main+xml"/>
                <Override PartName="/xl/worksheets/sheet1.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.worksheet+xml"/>
                <Override PartName="/xl/styles.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.styles+xml"/>
                <Override PartName="/xl/drawings/drawing1.xml" ContentType="application/vnd.openxmlformats-officedocument.drawing+xml"/>
                </Types>
                XML),
            '_rels/.rels' => $this->xml(<<<'XML'
                <Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">
                <Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/officeDocument" Target="xl/workbook.xml"/>
                </Relationships>
                XML),
            'xl/workbook.xml' => $this->workbook(),
            'xl/_rels/workbook.xml.rels' => $this->xml(<<<'XML'
                <Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">
                <Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/worksheet" Target="worksheets/sheet1.xml"/>
                <Relationship Id="rId2" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/styles" Target="styles.xml"/>
                </Relationships>
                XML),
            'xl/styles.xml' => $this->styles(),
            'xl/worksheets/sheet1.xml' => $this->worksheet(),
            'xl/worksheets/_rels/sheet1.xml.rels' => $this->xml(<<<'XML'
                <Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">
                <Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/drawing" Target="../drawings/drawing1.xml"/>
                </Relationships>
                XML),
            'xl/drawings/drawing1.xml' => $this->logoDrawing(),
            'xl/drawings/_rels/drawing1.xml.rels' => $this->xml(<<<'XML'
                <Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">
                <Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/image" Target="../media/image1.png"/>
                </Relationships>
                XML),
            'xl/media/image1.png' => file_get_contents(self::logoPath()),
        ];
    }

    /**
     * The one sheet, with the heading row named as the print titles so it repeats on every printed page.
     */
    private function workbook(): string
    {
        $sheetName = $this->escape(self::SHEET_NAME);
        $printTitles = sprintf("'%s'!\$%d:\$%d", $sheetName, self::HEADING_ROW, self::HEADING_ROW);

        return $this->xml(<<<XML
            <workbook xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main" xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships">
            <sheets><sheet name="{$sheetName}" sheetId="1" r:id="rId1"/></sheets>
            <definedNames><definedName name="_xlnm.Print_Titles" localSheetId="0">{$printTitles}</definedName></definedNames>
            <calcPr fullCalcOnLoad="1"/>
            </workbook>
            XML);
    }

    /**
     * The cell formats, numbered as the STYLE_ constants: amounts carry the
     * peso format, and the student table is ruled so it prints as a grid.
     */
    private function styles(): string
    {
        return $this->xml(<<<'XML'
            <styleSheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main">
            <numFmts count="1"><numFmt numFmtId="164" formatCode="&quot;₱&quot;#,##0.00"/></numFmts>
            <fonts count="3">
            <font><sz val="11"/><name val="Calibri"/><family val="2"/></font>
            <font><b/><sz val="11"/><name val="Calibri"/><family val="2"/></font>
            <font><b/><sz val="14"/><name val="Calibri"/><family val="2"/></font>
            </fonts>
            <fills count="3">
            <fill><patternFill patternType="none"/></fill>
            <fill><patternFill patternType="gray125"/></fill>
            <fill><patternFill patternType="solid"><fgColor rgb="FFE5E7EB"/><bgColor indexed="64"/></patternFill></fill>
            </fills>
            <borders count="3">
            <border><left/><right/><top/><bottom/><diagonal/></border>
            <border><left style="thin"><color auto="1"/></left><right style="thin"><color auto="1"/></right><top style="thin"><color auto="1"/></top><bottom style="thin"><color auto="1"/></bottom><diagonal/></border>
            <border><left/><right/><top style="thin"><color auto="1"/></top><bottom/><diagonal/></border>
            </borders>
            <cellStyleXfs count="1"><xf numFmtId="0" fontId="0" fillId="0" borderId="0"/></cellStyleXfs>
            <cellXfs count="11">
            <xf numFmtId="0" fontId="0" fillId="0" borderId="0" xfId="0"/>
            <xf numFmtId="0" fontId="1" fillId="0" borderId="0" xfId="0" applyFont="1" applyAlignment="1"><alignment horizontal="center"/></xf>
            <xf numFmtId="0" fontId="0" fillId="0" borderId="0" xfId="0" applyAlignment="1"><alignment horizontal="center"/></xf>
            <xf numFmtId="0" fontId="2" fillId="0" borderId="0" xfId="0" applyFont="1" applyAlignment="1"><alignment horizontal="center"/></xf>
            <xf numFmtId="0" fontId="1" fillId="2" borderId="1" xfId="0" applyFont="1" applyFill="1" applyBorder="1" applyAlignment="1"><alignment horizontal="center" vertical="center" wrapText="1"/></xf>
            <xf numFmtId="0" fontId="0" fillId="0" borderId="1" xfId="0" applyBorder="1" applyAlignment="1"><alignment vertical="center"/></xf>
            <xf numFmtId="0" fontId="0" fillId="0" borderId="1" xfId="0" applyBorder="1" applyAlignment="1"><alignment horizontal="center" vertical="center"/></xf>
            <xf numFmtId="164" fontId="0" fillId="0" borderId="1" xfId="0" applyNumberFormat="1" applyBorder="1" applyAlignment="1"><alignment vertical="center"/></xf>
            <xf numFmtId="0" fontId="1" fillId="0" borderId="1" xfId="0" applyFont="1" applyBorder="1" applyAlignment="1"><alignment horizontal="right" vertical="center"/></xf>
            <xf numFmtId="164" fontId="1" fillId="0" borderId="1" xfId="0" applyNumberFormat="1" applyFont="1" applyBorder="1" applyAlignment="1"><alignment vertical="center"/></xf>
            <xf numFmtId="0" fontId="0" fillId="0" borderId="2" xfId="0" applyBorder="1" applyAlignment="1"><alignment horizontal="center"/></xf>
            </cellXfs>
            <cellStyles count="1"><cellStyle name="Normal" xfId="0" builtinId="0"/></cellStyles>
            </styleSheet>
            XML);
    }

    /**
     * The sheet: the masthead, the student table and its total, then the signature lines.
     */
    private function worksheet(): string
    {
        $this->merges = [];
        $passedCount = $this->passedApplications->count();
        $share = $this->scholarship->fundShareInCentavos($passedCount);
        $columns = $this->columns();

        $rows = [
            $this->banner(1, __('Cavite State University'), self::STYLE_BOLD_CENTERED),
            $this->banner(2, __('Office of Student Affairs and Services'), self::STYLE_CENTERED),
            $this->banner(3, __('Fund Allocation'), self::STYLE_TITLE),
            $this->banner(4, $this->scholarship->title, self::STYLE_BOLD_CENTERED),
            $this->row(self::HEADING_ROW, array_map(
                fn (string $column): string => $this->textCell($column.self::HEADING_ROW, $columns[$column]['heading'], self::STYLE_HEADING),
                array_keys($columns),
            ), self::TABLE_ROW_HEIGHT),
        ];

        $row = self::HEADING_ROW;

        foreach ($this->passedApplications->values() as $index => $application) {
            $row++;
            $student = $application->user;

            $rows[] = $this->row($row, [
                $this->numberCell("A{$row}", (string) ($index + 1), self::STYLE_CELL_CENTERED),
                $this->textCell("B{$row}", $student->student_number, self::STYLE_CELL),
                $this->textCell("C{$row}", $this->upper($student->last_name ?? $student->name), self::STYLE_CELL),
                $this->textCell("D{$row}", $this->upper($student->first_name), self::STYLE_CELL),
                $this->textCell("E{$row}", $this->upper($student->middle_name), self::STYLE_CELL),
                $this->textCell("F{$row}", CvsuPrograms::abbreviation($student->course), self::STYLE_CELL),
                $this->numberCell("G{$row}", $share === null ? null : Peso::fromCentavos($share), self::STYLE_CELL_AMOUNT),
                $this->textCell("H{$row}", null, self::STYLE_CELL),
            ], self::TABLE_ROW_HEIGHT);
        }

        $row++;

        if ($passedCount === 0) {
            $rows[] = $this->row($row, $this->span($row, 'A', self::LAST_COLUMN, __('No students have passed this scholarship yet.'), self::STYLE_CELL_CENTERED), self::TABLE_ROW_HEIGHT);
        } else {
            $rows[] = $this->row($row, [
                ...$this->span($row, 'A', 'F', __('Total'), self::STYLE_TOTAL_LABEL),
                $share === null
                    ? $this->numberCell("G{$row}", null, self::STYLE_TOTAL_AMOUNT)
                    : $this->formulaCell("G{$row}", sprintf('SUM(G%d:G%d)', self::HEADING_ROW + 1, $row - 1), Peso::fromCentavos($share * $passedCount), self::STYLE_TOTAL_AMOUNT),
                $this->textCell("H{$row}", null, self::STYLE_CELL),
            ], self::TABLE_ROW_HEIGHT);
        }

        $row += 2;
        $rows[] = $this->row($row, [
            $this->textCell("B{$row}", __('Prepared by:'), self::STYLE_PLAIN),
            $this->textCell("F{$row}", __('Noted by:'), self::STYLE_PLAIN),
        ]);

        $row += 2;
        $rows[] = $this->row($row, [
            ...$this->span($row, 'B', 'D', $this->upper($this->preparedBy->surname_first_name), self::STYLE_SIGNATURE_LINE),
            ...$this->span($row, 'F', 'H', null, self::STYLE_SIGNATURE_LINE),
        ]);

        $widths = '';

        foreach (array_values($columns) as $index => $column) {
            $widths .= sprintf('<col min="%1$d" max="%1$d" width="%2$d" customWidth="1"/>', $index + 1, $column['width']);
        }

        $merges = implode('', array_map(fn (string $range): string => '<mergeCell ref="'.$range.'"/>', $this->merges));
        $footer = $this->escape(sprintf(
            '&L&8%s&R&8%s &P %s &N',
            __('Generated :date by the OSAS Scholarship Allocation System', ['date' => $this->generatedAt->format('M j, Y g:i A')]),
            __('Page'),
            __('of'),
        ));

        return $this->xml(
            '<worksheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main" xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships">'
            .'<sheetPr><pageSetUpPr fitToPage="1"/></sheetPr>'
            .sprintf('<sheetViews><sheetView workbookViewId="0"><pane ySplit="%d" topLeftCell="A%d" activePane="bottomLeft" state="frozen"/></sheetView></sheetViews>', self::HEADING_ROW, self::HEADING_ROW + 1)
            .'<cols>'.$widths.'</cols>'
            .'<sheetData>'.implode('', $rows).'</sheetData>'
            .sprintf('<mergeCells count="%d">%s</mergeCells>', count($this->merges), $merges)
            .'<printOptions horizontalCentered="1"/>'
            .'<pageMargins left="0.4" right="0.4" top="0.5" bottom="0.6" header="0.3" footer="0.3"/>'
            .'<pageSetup paperSize="9" orientation="landscape" fitToWidth="1" fitToHeight="0"/>'
            .'<headerFooter><oddFooter>'.$footer.'</oddFooter></headerFooter>'
            .'<drawing r:id="rId1"/>'
            .'</worksheet>'
        );
    }

    /**
     * The student table's columns, in the order OSAS's payroll sheet runs, with their widths in characters.
     *
     * @return array<string, array{heading: string, width: int}>
     */
    private function columns(): array
    {
        return [
            'A' => ['heading' => __('SEQ No.'), 'width' => 8],
            'B' => ['heading' => __('Student Number'), 'width' => 16],
            'C' => ['heading' => __('Last Name'), 'width' => 22],
            'D' => ['heading' => __('First Name'), 'width' => 24],
            'E' => ['heading' => __('Middle Name'), 'width' => 20],
            'F' => ['heading' => __('Course'), 'width' => 14],
            'G' => ['heading' => __('Amount Received'), 'width' => 18],
            'H' => ['heading' => __('Signature'), 'width' => 34],
        ];
    }

    /**
     * The CvSU logo pinned to the sheet's top-left corner, beside the masthead.
     *
     * A picture's size is in EMUs (12,700 to the point). The width follows
     * the PNG's own proportions, read with getimagesize(), which needs no GD.
     */
    private function logoDrawing(): string
    {
        [$width, $height] = getimagesize(self::logoPath());
        $tall = self::LOGO_HEIGHT * 12700;
        $wide = intdiv($tall * $width, $height);
        $description = $this->escape(__('Cavite State University logo'));

        return $this->xml(<<<XML
            <xdr:wsDr xmlns:xdr="http://schemas.openxmlformats.org/drawingml/2006/spreadsheetDrawing" xmlns:a="http://schemas.openxmlformats.org/drawingml/2006/main" xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships">
            <xdr:oneCellAnchor>
            <xdr:from><xdr:col>0</xdr:col><xdr:colOff>38100</xdr:colOff><xdr:row>0</xdr:row><xdr:rowOff>25400</xdr:rowOff></xdr:from>
            <xdr:ext cx="{$wide}" cy="{$tall}"/>
            <xdr:pic>
            <xdr:nvPicPr><xdr:cNvPr id="2" name="CvSU logo" descr="{$description}"/><xdr:cNvPicPr><a:picLocks noChangeAspect="1"/></xdr:cNvPicPr></xdr:nvPicPr>
            <xdr:blipFill><a:blip r:embed="rId1"/><a:stretch><a:fillRect/></a:stretch></xdr:blipFill>
            <xdr:spPr><a:xfrm><a:off x="0" y="0"/><a:ext cx="{$wide}" cy="{$tall}"/></a:xfrm><a:prstGeom prst="rect"><a:avLst/></a:prstGeom></xdr:spPr>
            </xdr:pic>
            <xdr:clientData/>
            </xdr:oneCellAnchor>
            </xdr:wsDr>
            XML);
    }

    /**
     * The official CvSU logo, as published on cvsu.edu.ph.
     */
    private static function logoPath(): string
    {
        return resource_path('images/cvsu-logo.png');
    }

    /**
     * A row holding one line of text centred across the whole table.
     */
    private function banner(int $row, string $text, int $style): string
    {
        return $this->row($row, $this->span($row, 'A', self::LAST_COLUMN, $text, $style));
    }

    /**
     * Merged cells from one column to another, every one styled so a border runs the whole span.
     *
     * @return list<string>
     */
    private function span(int $row, string $fromColumn, string $toColumn, ?string $text, int $style): array
    {
        $this->merges[] = "{$fromColumn}{$row}:{$toColumn}{$row}";

        return array_map(
            fn (string $column): string => $this->textCell($column.$row, $column === $fromColumn ? $text : null, $style),
            range($fromColumn, $toColumn),
        );
    }

    /**
     * @param  list<string>  $cells
     */
    private function row(int $row, array $cells, ?int $height = null): string
    {
        $size = $height === null ? '' : sprintf(' ht="%d" customHeight="1"', $height);

        return sprintf('<row r="%d"%s>%s</row>', $row, $size, implode('', $cells));
    }

    /**
     * A text cell. Student numbers go in as text, so Excel keeps them exactly as typed.
     */
    private function textCell(string $reference, ?string $text, int $style): string
    {
        if ($text === null || $text === '') {
            return sprintf('<c r="%s" s="%d"/>', $reference, $style);
        }

        return sprintf('<c r="%s" s="%d" t="inlineStr"><is><t>%s</t></is></c>', $reference, $style, $this->escape($text));
    }

    /**
     * A number cell, from a numeric string such as Peso::fromCentavos() gives, so amounts never pass through a float.
     */
    private function numberCell(string $reference, ?string $number, int $style): string
    {
        if ($number === null) {
            return sprintf('<c r="%s" s="%d"/>', $reference, $style);
        }

        return sprintf('<c r="%s" s="%d"><v>%s</v></c>', $reference, $style, $number);
    }

    /**
     * A formula cell, with its value worked out here so the sheet shows it even before Excel recalculates.
     */
    private function formulaCell(string $reference, string $formula, string $value, int $style): string
    {
        return sprintf('<c r="%s" s="%d"><f>%s</f><v>%s</v></c>', $reference, $style, $formula, $value);
    }

    /**
     * A name in capitals, as every page of the system shows names (asked 2026-10-25).
     */
    private function upper(?string $name): ?string
    {
        return $name === null ? null : mb_strtoupper($name);
    }

    /**
     * Escape text for XML, dropping the control characters XML 1.0 cannot carry at all.
     */
    private function escape(string $text): string
    {
        return htmlspecialchars(preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F]/', '', $text), ENT_XML1 | ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    }

    private function xml(string $body): string
    {
        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'."\n".$body;
    }
}
