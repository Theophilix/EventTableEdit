<?php
/**
 * $Id:$.
 *
 * @copyright (C) 2007 - 2020 Manuel Kaspar and Theophilix / Filter by unimx
 * @license GNU/GPL, see LICENSE.php in the installation package
 * This file is part of Event Table Edit
 *
 * Event Table Edit is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.

 * Event Table Edit is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.

 * You should have received a copy of the GNU General Public License
 * along with Event Table Edit. If not, see <http://www.gnu.org/licenses/>.
 */
defined('_JEXEC') or die;

class Csv
{
    public static function getValuesFromCsv($row, $separator = ';', $escapeChar = '"')
    {
		
        $tokens = [];
        $i = 0;
        $len = strlen($row);
        $inEscapeSeq = false;
        $currToken = '';

        while ($i < $len) {
            $c = substr($row, $i, 1);

            if ($inEscapeSeq) {
                if ($c === $escapeChar) {
                    // lookahead to see if next character is also an escape char
                    if ($i === ($len - 1)) {
                        // c is last char, so must be end of escape sequence
                        $inEscapeSeq = false;
                    } elseif (substr($row, $i + 1, 1) === $escapeChar) {
                        // append literal escape char
                        $currToken .= $escapeChar;
                        ++$i;
                    } else {
                        // end of escape sequence
                        $inEscapeSeq = false;
                    }
                } else {
                    $currToken .= $c;
                }
            } else {
                if ($c === $separator) {
                    // end of token, flush it
                    array_push($tokens, $currToken);
                    $currToken = '';
                } elseif ($c === $escapeChar) {
                    // begin escape sequence
                    $inEscapeSeq = true;
                } else {
                    $currToken .= $c;
                }
            }
            ++$i;
        }
        // flush the last token
        array_push($tokens, $currToken);

        return $tokens;
    }

    /*
     * Generates the CSV data
     */
    public static function generateCsv( $rows, $separator = ';', $doubleqt = 1)
    {
        $csvfile = '';

        for ($s = 0; $s < count($rows); ++$s) {
            for ($r = 0; $r < count($rows[0]); ++$r) {
                //Add doublequotes to every cell, if yes was selected
                if (1 === (int)$doubleqt) {
                    $rows[$s][$r] = str_replace('"', '""', $rows[$s][$r]);
                    $rows[$s][$r] = '"'.$rows[$s][$r].'"';
                } else {
                    //Add doublequotes if the separator or doublequotes are inside the cell
                    if ((false !== strpos($rows[$s][$r], '"')) || (false !== strpos($rows[$s][$r], $separator))) {
                        $rows[$s][$r] = str_replace('"', '""', $rows[$s][$r]);
                        $rows[$s][$r] = '"'.$rows[$s][$r].'"';
                    }
                }
                $rows[$s][$r] = str_replace('<br />', 'csvcsv', $rows[$s][$r]);
                $csvfile .= $rows[$s][$r];
                if ($r < count($rows[$s]) - 1) {
                    $csvfile .= $separator;
                }
            }
            $csvfile .= "\n";
        }

        return $csvfile;
    }
}
