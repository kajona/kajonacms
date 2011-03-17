<?php
/**
 * File containing the ezcGraphPaletteTango class
 *
 * @package Graph
 * @version 1.5
 * @copyright Copyright (C) 2005-2009 eZ Systems AS. All rights reserved.
 * @license http://ez.no/licenses/new_bsd New BSD License
 */
/**
 * Light color pallet for ezcGraph based on Tango style guidelines at
 * http://tango-project.org/Generic_Icon_Theme_Guidelines
 *
 * @version 1.5
 * @package Graph
 */
class ezcGraphPaletteKajona extends ezcGraphPalette
{
    /**
     * Axiscolor
     *
     * @var ezcGraphColor
     */
    protected $axisColor = '#2E3436';

    /**
     * Array with colors for datasets
     *
     * @var array
     */
    protected $dataSetColor = array(
        "#BCE02E", "#E0642E", "#E0D62E", "#2E97E0", "#B02EE0", "#E02E75", "#5CE02E", "#E0B02E", "#527C94", "#99993d", "#ff0000"
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
