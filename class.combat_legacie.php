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
	private $_DEFENDER = array();
	private $_DEFENSEUR = array();
	private $_TechnoDef = array();
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
	}
	
	function pretty_number($n, $floor = true) {
		if ($floor) {
			$n = floor($n);
		}
		return number_format($n, 0, ",", ".");
	}

	private function shell($type,$amount){
		return (($this->_Pricelist[$type]['metal'] + $this->_Pricelist[$type]['crystal'])/10) * $amount;
	}

	private function shield($type,$amount){
		return $this->_Combatcap[$type]['shield'] * $amount;
	}

	private function weapon($type,$amount){
		return $this->_Combatcap[$type]['attack'] * $amount;
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
	
	private function processing($shield,$shell,$weapon,$typeAtt,$amountAtt,$typeDef,$amountDef,$TechnoAtt,$TechnoDef,$Rapidfire = false){
		global $lang;
		
		$FacultatifamountDef = $amountDef;
		$FacultatifShieldDef[$typeDef] = $shield;
		$FacultatifCoqueDef[$typeDef] = $shell;
		$FacultatifArmeAtt[$typeAtt] = $weapon;
		
		$ShieldDef[$typeDef] = $shield;
		$CoqueDef[$typeDef] = $shell;
		$ArmeAtt[$typeAtt] = $weapon;
		
		# last system
		// $ArmeAtt[$typeAtt] = $this->weapon($typeAtt,$amountAtt) * (1 + (0.1 * ($TechnoAtt["military_tech"])));
				
		var_dump("Les ".$lang['tech'][$typeAtt]." (".$this->pretty_number($amountAtt).") Tire sur les " .$lang['tech'][$typeDef] ." (".$this->pretty_number($amountDef).")");
		if($ArmeAtt[$typeAtt] > $ShieldDef[$typeDef]){
			
			var_dump("La puissance des ".$lang['tech'][$typeAtt]." (".$this->pretty_number($ArmeAtt[$typeAtt]).") etant superieur au bouclier (".$this->pretty_number($ShieldDef[$typeDef]).") des ".$lang['tech'][$typeDef]." , les ".$lang['tech'][$typeDef]." n'ont plus de bouclier");			
			$ArmeAtt[$typeAtt] -= $ShieldDef[$typeDef];
			var_dump("Il reste ".$this->pretty_number($ArmeAtt[$typeAtt])." de puissance au ".$lang['tech'][$typeAtt]."");
			if($ArmeAtt[$typeAtt] >= $CoqueDef[$typeDef]){
				var_dump("la puissance des  ".$lang['tech'][$typeAtt]." (".$this->pretty_number($ArmeAtt[$typeAtt]).") est superieur a la coque (".$this->pretty_number($CoqueDef[$typeDef]).") des ".$lang['tech'][$typeDef].", les ".$lang['tech'][$typeDef]." sont detruits");
				$ArmeAtt[$typeAtt] -= $CoqueDef[$typeDef];
				$CoqueDef [$typeDef]= 0;
				$ShieldDef[$typeDef] = 0;
				$amountDef = 0;
			}else{
				var_dump("la coque (".$this->pretty_number($CoqueDef[$typeDef]).") des ".$lang['tech'][$typeDef]." ont resiste a la puissance des ".$lang['tech'][$typeAtt]." (".$this->pretty_number($ArmeAtt[$typeAtt]).")");
				$coqueBefore[$typeDef] = $CoqueDef[$typeDef];
				$CoqueDef[$typeDef] -= $ArmeAtt[$typeAtt];
				$ArmeAtt[$typeAtt] = 0;
				var_dump("il reste (".$this->pretty_number($CoqueDef[$typeDef]).") de structure coque aux ".$lang['tech'][$typeDef]." sur ".$this->pretty_number($coqueBefore[$typeDef])."");
				$ShieldDef[$typeDef] = 0;
				$amountDef = round((($CoqueDef[$typeDef]/$coqueBefore[$typeDef])*$amountDef));
			}
		}else{
			$ShieldDef[$typeDef] -= $ArmeAtt[$typeAtt];
			var_dump("le bouclier des  ".$lang['tech'][$typeDef]." a absorbe ,il reste ".$this->pretty_number($ShieldDef[$typeDef])." de bouclier");
		}
		
		return array(
				$FacultatifamountDef,
				$FacultatifShieldDef[$typeDef],
				$FacultatifCoqueDef[$typeDef],
				$FacultatifArmeAtt[$typeAtt]
		);
	}
	
	private function PhaseAttaquant(){
		
		foreach($this->ROUNDS[$this->_round]['Defenseur'] AS $typeDef=>$amountDef){

			$amountDef = $this->ROUNDS[$this->_round]['Defenseur'][$typeDef];
			$rand = rand(80, 120) / 100;
			$ShieldDef = $this->shield($typeDef,$amountDef) * (1 + (0.1 * ($this->_TechnoDef["shield_tech"]))) * $rand;
			$rand = rand(80, 120) / 100;
			$CoqueDef  = $this->shell($typeDef,$amountDef) * (1 + (0.1 * ($this->_TechnoDef["defence_tech"]))) * $rand;
			
			foreach($this->ROUNDS[$this->_round]['Attaquant'] AS $typeAtt=>$amountAtt){
				if($amountDef > 0 && $amountAtt > 0){
					if(!isset($Lastweapon)){
						$weapon = $this->weapon($typeAtt,$amountAtt) * (1 + (0.1 * ($this->_TechnoAtt["military_tech"])));
					}else{
						$weapon = $Lastweapon;
					}
					// $weapon = $this->weapon($typeAtt,$amountAtt) * (1 + (0.1 * ($this->_TechnoAtt["military_tech"]))) / $Lastweapon * $amountAtt;
					if(round((($this->shell($typeDef,$amountDef)/$this->shell($typeDef,$this->_DEFENSEUR[$typeDef])) * 100)) > 30){
						var_dump("Phase de combat Attaquant");
						$result = $this->processing($ShieldDef,$CoqueDef,$weapon,$typeAtt,$amountAtt,$typeDef,$amountDef,$this->_TechnoAtt,$this->_TechnoDef);
						$amountDef = $result[0];
						$ShieldDef = $result[1];
						$CoqueDef = $result[2];
						$weapon = $result[3];
						if($weapon <= 0){
							$this->_ATTACKER[$typeAtt] = intval($amountAtt);
							$this->_DEFENDER[$typeDef] = intval($amountDef);
							$this->ROUNDS[$this->_round] = array(
								'Attaquant'=>$this->_ATTACKER,
								'Defenseur'=>$this->_DEFENDER
							);
							break(1);
						}else{
							$Lastweapon = $result[3];
						}
					}else{
						if(abs(round((($this->shell($typeDef,$amountDef)/$this->shell($typeDef,$this->_DEFENSEUR[$typeDef])) * 100)) - 100) >= rand(1,100)){
							$this->_ATTACKER[$typeAtt] = intval($amountAtt);
							$this->_DEFENDER[$typeDef] = intval(0);
							$this->ROUNDS[$this->_round] = array(
								'Attaquant'=>$this->_ATTACKER,
								'Defenseur'=>$this->_DEFENDER
							);
						}
					}
				}else{
					$this->_ATTACKER[$typeAtt] = intval($amountAtt);
					$this->_DEFENDER[$typeDef] = 0;
					$this->ROUNDS[$this->_round] = array(
						'Attaquant'=>$this->_ATTACKER,
						'Defenseur'=>$this->_DEFENDER
					);
				}
			}
		}
	}
	

	private function PhaseDefenseur(){
		foreach($this->ROUNDS[$this->_round]['Attaquant'] AS $typeDef=>$amountDef){	
			
			$amountDef = $this->ROUNDS[$this->_round]['Attaquant'][$typeDef];
			$rand = rand(80, 120) / 100;
			$ShieldDef = $this->shield($typeDef,$amountDef) * (1 + (0.1 * ($this->_TechnoAtt["shield_tech"]))) * $rand;
			$rand = rand(80, 120) / 100;
			$CoqueDef  = $this->shell($typeDef,$amountDef) * (1 + (0.1 * ($this->_TechnoAtt["defence_tech"]))) * $rand;
			
			foreach($this->ROUNDS[$this->_round]['Defenseur'] AS $typeAtt=>$amountAtt){
				if($amountDef > 0 && $amountAtt > 0){
					if(!isset($Lastweapon)){
						$weapon = $this->weapon($typeAtt,$amountAtt) * (1 + (0.1 * ($this->_TechnoDef["military_tech"])));
					}else{
						$weapon = $Lastweapon;
					}
					
					if(round((($this->shell($typeDef,$amountDef)/$this->shell($typeDef,$this->_ATTAQUANT[$typeDef])) * 100)) > 30){
						
						var_dump("Phase de combat Defenseur");	
						$result = $this->processing($ShieldDef,$CoqueDef,$weapon,$typeAtt,$amountAtt,$typeDef,$amountDef,$this->_TechnoDef,$this->_TechnoAtt);
						$amountDef = $result[0];
						$ShieldDef = $result[1];
						$CoqueDef = $result[2];
						$weapon = $result[3];
						if($weapon <= 0){
							$this->_ATTACKER[$typeDef] = intval($amountDef);
							$this->_DEFENDER[$typeAtt] = intval($amountAtt);
							$this->ROUNDS[$this->_round] = array(
								'Attaquant'=>$this->_ATTACKER,
								'Defenseur'=>$this->_DEFENDER
							);
							break(1);
						}else{
							$Lastweapon = $result[3];
						}
					}else{
						if(abs(round((($this->shell($typeDef,$amountDef)/$this->shell($typeDef,$this->_ATTAQUANT[$typeDef])) * 100)) - 100) >= rand(1,100)){
							$amountDef = 0;
							$this->_ATTACKER[$typeDef] = intval($amountDef);
							$this->_DEFENDER[$typeAtt] = intval(0);
							$this->ROUNDS[$this->_round] = array(
								'Attaquant'=>$this->_ATTACKER,
								'Defenseur'=>$this->_DEFENDER
							);
						}
					}
				}else{
					$this->_ATTACKER[$typeDef] = intval($amountDef);
					$this->_DEFENDER[$typeAtt] = intval(0);
					$this->ROUNDS[$this->_round] = array(
						'Attaquant'=>$this->_ATTACKER,
						'Defenseur'=>$this->_DEFENDER
					);
				}
			}
		}
	}
	
	private function PhaseRapidFire($type,$CoqueDef,$ShieldDef,$Attaquant,$Defendeur,$typeDef,$amountDef){		
		global $lang;
		foreach($Attaquant AS $typeAtt=>$amountAtt){
			if($this->_Combatcap[$typeAtt]['sd'][$typeDef] > 1){
				var_dump("il y a un rapidfire du  ".$lang['tech'][$typeAtt]." sur le  ".$lang['tech'][$typeDef]."");
				$AmountRF = 0;
				for($i = 1;$i <= $amountAtt;$i++){
					if(rand(1,100) <= round((1-(1/$this->_Combatcap[$typeAtt]['sd'][$typeDef])) * 100)){
						$AmountRF ++;
					}else{
						$AmountRF --;
					}
				}
				
				$AmountRF = ($AmountRF / 100);
				var_dump($AmountRF);
				if($amountDef > 0 && $amountAtt > 0){
					if($type == 'Defendeur'){
						$weapon = $this->weapon($typeAtt,$amountAtt) * (1 + (0.1 * ($this->_TechnoDef["military_tech"]))) * $AmountRF;
					}else{
						$weapon = $this->weapon($typeAtt,$amountAtt) * (1 + (0.1 * ($this->_TechnoAtt["military_tech"]))) * $AmountRF;
					}
					
					if(round((($this->shell($typeDef,$amountDef)/$this->shell($typeDef,$Defendeur[$typeDef])) * 100)) > 30){
						
						$result = $this->processing($ShieldDef,$CoqueDef,$weapon,$typeAtt,$amountAtt,$typeDef,$amountDef,$this->_TechnoDef,$this->_TechnoAtt);
						$amountDef = $result[0];
						$ShieldDef = $result[1];
						$CoqueDef = $result[2];
						
					}else{
						if(abs(round((($this->shell($typeDef,$amountDef)/$this->shell($typeDef,$Defendeur[$typeDef])) * 100)) - 100) >= rand(1,100)){
							$amountDef = 0;
						}
					}
				}
			}
			if($type == 'Defendeur'){
				$this->_ATTACKER[$typeDef] = intval($amountDef);
				$this->_DEFENDER[$typeAtt] = intval($amountAtt);
			}else{
				$this->_ATTACKER[$typeAtt] = intval($amountAtt);
				$this->_DEFENDER[$typeDef] = intval($amountDef);
			}
		}

		return array($this->_ATTACKER,$this->_DEFENDER);
	}
	
	public function fight(){
		
		for($this->_round;$this->_round <= self::TOURS;$this->_round ++)
		{
				
				$this->ROUNDS[$this->_round] = array(
					'Attaquant'=>$this->_ATTACKER,
					'Defenseur'=>$this->_DEFENDER
				);
				
				if($this->TotalAmount($this->_ATTACKER) > 0 && $this->TotalAmount($this->_DEFENDER) > 0)
				{
					var_dump("Tour " . $this->_round);

					
					$this->PhaseAttaquant();

					#phase de RF 
					foreach($this->ROUNDS[$this->_round]['Defenseur'] AS $typeDef=>$amountDef){

						$amountDef = $this->ROUNDS[$this->_round]['Defenseur'][$typeDef];

						$ShieldDef = $this->shield($typeDef,$amountDef) * (1 + (0.1 * ($this->_TechnoDef["shield_tech"])));
						
						$CoqueDef  = $this->shell($typeDef,$amountDef) * (1 + (0.1 * ($this->_TechnoDef["defence_tech"])));					
						if($amountDef > 0){
							$RapidFireResult = $this->PhaseRapidFire('Attacker',$CoqueDef,$ShieldDef,$this->_ATTACKER,$this->_DEFENDER,$typeDef,$amountDef);
							
							$this->ROUNDS[$this->_round] = array(
								'Attaquant'=>$RapidFireResult[0],
								'Defenseur'=>$RapidFireResult[1]
							);
						}
					}
					
					$this->PhaseDefenseur();
					
					foreach($this->ROUNDS[$this->_round]['Attaquant'] AS $typeDef=>$amountDef){	
						
						$amountDef = $this->ROUNDS[$this->_round]['Attaquant'][$typeDef];
						
						$ShieldDef = $this->shield($typeDef,$amountDef) * (1 + (0.1 * ($this->_TechnoAtt["shield_tech"])));
						
						$CoqueDef  = $this->shell($typeDef,$amountDef) * (1 + (0.1 * ($this->_TechnoAtt["defence_tech"])));

						if($amountDef > 0){
							$RapidFireResult = $this->PhaseRapidFire('Defendeur',$CoqueDef,$ShieldDef,$this->_DEFENDER,$this->_ATTACKER,$typeDef,$amountDef);
							
							$this->ROUNDS[$this->_round] = array(
								'Attaquant'=>$RapidFireResult[0],
								'Defenseur'=>$RapidFireResult[1]
							);
						}
					}
					
					var_dump($this->ROUNDS[$this->_round]);
					
				}else{
				var_dump("Fin du Combat au Tour ".$this->_round);
				break;
			}
		}
		return true;
	}
}
?>