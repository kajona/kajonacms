<?php
/**
 * Default color palette, parametrized for kajona @ ez components.
 *
 * @package modul_system
 * @author sidler@mulchprod.de
 * @since 3.4
 */
class ezcGraphPaletteKajona extends ezcGraphPalette
{
    /**
     * Axiscolor
     *
     * @var ezcGraphColor
     */
    protected $axisColor = '#AFAFAF';

    /**
     * Array with colors for datasets
     *
     * @var array
     */
    protected $dataSetColor = array(
        "#BCE02E", "#E0642E", "#2E97E0", "#E0D62E",  "#B02EE0", "#E02E75", "#5CE02E", "#E0B02E", "#527C94", "#99993d", "#ff0000"
    );

    /**
     * Array with symbols for datasets
     *
     * @var array
     */
    protected $dataSetSymbol = array(
        ezcGraph::NO_SYMBOL,
    );

    /**
     * Name of font to use
     *
     * @var string
     */
    protected $fontName = 'sans-serif';

    /**
     * Fontcolor
     *
     * @var ezcGraphColor
     */
    protected $fontColor = '#2E3436';

    /**
     * Backgroundcolor for chart
     *
     * @var ezcGraphColor
     */
    protected $chartBackground = '#EEEEEC';

    /**
     * Padding in elements
     *
     * @var integer
     */
    protected $padding = 0;

    /**
     * Margin of elements
     *
     * @var integer
     */
    protected $margin = 0;

    /**
     * Color of grid lines
     *
     * @var ezcGraphColor
     */
    protected $majorGridColor = '#AFAFAF';

    /**
     * Color of minor grid lines
     *
     * @var ezcGraphColor
     */
    protected $minorGridColor = '#DFDFDF';
}

?>
