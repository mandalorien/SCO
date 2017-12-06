<?php
/**
 * Tis file is part of XNova:Legacies
 *
 * @license http://www.gnu.org/licenses/gpl-3.0.txt
 * @see http://www.xnova-ng.org/
 *
 * Copyright (c) 2009-Present, XNova Support Team <http://www.xnova-ng.org>
 * All rights reserved.
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 *                                --> NOTICE <--
 *  This file is part of the core development branch, changing its contents will
 * make you unable to use the automatic updates manager. Please refer to the
 * documentation for further information about customizing XNova.
 * 
 * use by mandalorien for Project - SCO
 */

class Combat{
	
	const TOURS = 6;
	private $_round;
	private $_ROUNDS = array();
	private $_ATTACKER = array();
	private $_ATTAQUANT = array();
	private $_TechnoAtt = array();
	private $_PowerAtt;
	private $_DEFENDER = array();
	private $_DEFENSEUR = array();
	private $_TechnoDef = array();
	private $_PowerDef;
	private $_Pricelist = array();
	private $_Combatcap = array();
	
	
	public function __construct($A,$B,$TA,$TB){
		global $pricelist,$CombatCaps;
		$this->_round = 1;
		$this->_ATTACKER = $A;
		$this->_DEFENDER = $B;
		$this->_ATTAQUANT = $A;
		$this->_DEFENSEUR = $B;
		$this->_TechnoAtt = $TA;
		$this->_TechnoDef = $TB;
		$this->_Pricelist = $pricelist;
		$this->_Combatcap = $CombatCaps;
		
		$this->_PowerAtt = $this->TotalPower($this->_ATTACKER,$this->_TechnoAtt);
		$this->_PowerDef = $this->TotalPower($this->_DEFENDER,$this->_TechnoDef);
	}
	
	function pretty_number($n, $floor = true) {
		if ($floor) {
			$n = floor($n);
		}
		return number_format($n, 0, ",", ".");
	}

	private function shell($type,$nombre){
		return (($this->_Pricelist[$type]['metal'] + $this->_Pricelist[$type]['crystal'])/10) * $nombre;
	}

	private function shield($type,$nombre){
		return $this->_Combatcap[$type]['shield'] * $nombre;
	}

	private function weapon($type,$nombre){
		return $this->_Combatcap[$type]['attack'] * $nombre;
	}
	
	private function TotalShells($array,$techno){
		$toto = 0;
		foreach($array AS $type=>$amount){
			$toto += $this->shell($type,$amount);
		}
		return $toto * (1 + (0.1 * ($techno["defence_tech"])));
	}
	
	private function totalShields($array,$techno){
		$toto = 0;
		foreach($array AS $type=>$amount){
			$toto += $this->shield($type,$amount);
		}
		return $toto* (1 + (0.1 * ($techno["shield_tech"])));
	}
	
	private function totalWeapons($array,$techno){
		$toto = 0;
		foreach($array AS $type=>$amount){
			$toto += $this->weapon($type,$amount);
		}
		return $toto * (1 + (0.1 * ($techno["military_tech"])));
	}
	
	private function totalAmount($array){
		$toto = 0;
		foreach($array AS $type=>$amount){
			$toto += $amount;
		}
		return $toto;
	}
}
?>