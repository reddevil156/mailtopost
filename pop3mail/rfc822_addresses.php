<?php
/**
*
* @package Mail to Post Extension
* @copyright (c) 2019 david63
* @license GNU General Public License, version 2 (GPL-2.0)
*
* rfc822_addresses.php
*
* @(#) $Id: rfc822_addresses.php,v 1.16 2016/07/23 01:50:22 mlemos Exp $
*
**/

namespace david63\mailtopost\pop3mail;

class rfc822_addresses
{
	/**
	* Constructor for admin manage controller
	*
	* @access public
	*/
	public function __construct()
	{

	}

	/* Private variables */

	var $v = '';

	/* Public variables */
	var $error = '';
	var $error_position = -1;
	var $ignore_syntax_errors = 1;
	var $warnings = [];

	/* Private functions */

	function SetError($error)
	{
		$this->error = $error;
		return (0);
	}

	function SetPositionedError($error, $position)
	{
		$this->error_position = $position;
		return ($this->SetError($error));
	}

	function SetWarning($warning, $position)
	{
		$this->warnings[$position] = $warning;
		return (1);
	}

	function SetPositionedWarning($error, $position)
	{
		if (!$this->ignore_syntax_errors)
		{
			return ($this->SetPositionedError($error, $position));
		}
		return ($this->SetWarning($error, $position));
	}

	function QDecode($p, &$value, &$encoding)
	{
		$encoding = $charset = null;
		$s = 0;
		$decoded = '';
		$l = strlen($value);
		while ($s < $l)
		{
			if (gettype($q = strpos($value, '=?', $s)) != 'integer')
			{
				if ($s == 0)
				{
					return (1);
				}
				if ($s < $l)
				{
					$decoded .= substr($value, $s);
				}
				break;
			}
			if ($s < $q)
			{
				$decoded .= substr($value, $s, $q - $s);
			}
			$q += 2;
			if (gettype($c = strpos($value, '?', $q)) != 'integer' || $q == $c)
			{
				return ($this->SetPositionedWarning('invalid Q-encoding character set', $p + $q));
			}
			if (isset($charset))
			{
				$another_charset = strtolower(substr($value, $q, $c - $q));
				if (strcmp($charset, $another_charset) && strcmp($another_charset, 'ascii'))
				{
					return ($this->SetWarning('it is not possible to decode an encoded value using mixed character sets into a single value', $p + $q));
				}
			}
			else
			{
				$charset = strtolower(substr($value, $q, $c - $q));
				if (!strcmp($charset, 'ascii'))
				{
					$charset = null;
				}
			}
			++$c;
			if (gettype($t = strpos($value, '?', $c)) != 'integer' || $c==$t)
			{
				return ($this->SetPositionedWarning('invalid Q-encoding type', $p + $c));
			}
			$type = strtolower(substr($value, $c, $t - $c));
			++$t;
			if (gettype($e = strpos($value, '?=', $t)) != 'integer')
			{
				return ($this->SetPositionedWarning('invalid Q-encoding encoded data', $p + $e));
			}
			switch ($type)
			{
				case 'q':
					for ($s = $t; $s<$e;)
					{
						switch ($b = $value[$s])
						{
							case '=':
								$h = hexdec($hex = strtolower(substr($value, $s + 1, 2)));
								if ($s + 3 > $e || strcmp(sprintf('%02x', $h), $hex))
								{
									return ($this->SetPositionedWarning('invalid Q-encoding q encoded data', $p + $s));
								}
								$decoded .= chr($h);
								$s += 3;
							break;

							case '_':
								$decoded .= ' ';
								++$s;
							break;

							default:
								$decoded .= $b;
								++$s;
						}
					}
					break;

				case 'b':
					if ($e <= $t || strlen($binary = base64_decode($data = substr($value, $t, $e - $t))) == 0 || gettype($binary) != 'string')
					{
						return ($this->SetPositionedWarning('invalid Q-encoding b encoded data', $p + $t));
					}
					$decoded .= $binary;
					$s = $e;
					break;

				default:
					return ($this->SetPositionedWarning('Q-encoding '.$type.' is not yet supported', $p + $c));
			}
			$s += 2;
			$s += strspn($value, " \t", $s);
		}
		$value = $decoded;
		$encoding = $charset;
		return (1);
	}

	function ParseCText(&$p, &$c_text)
	{
		$c_text = null;
		$v = $this->v;
		if ($p<strlen($v) && gettype(strchr("\t\r\n ()\\\0", $c = $v[$p])) != 'string' && Ord($c)<128)
		{
			$c_text = $c;
			++$p;
		}
		return (1);
	}

	function ParseQText(&$p, &$q_text)
	{
		$q_text = null;
		$v = $this->v;
		if ($p>strlen($v) || gettype(strchr("\t\r\n \"\\\0", $c = $v[$p])) == 'string')
		{
			return (1);
		}
		if (ord($c) >= 128)
		{
			if (!$this->ignore_syntax_errors)
			{
				return (1);
			}
			$this->SetPositionedWarning('it was used an unencoded 8 bit character', $p);
		}
		$q_text = $c;
		++$p;
		return (1);
	}

	function ParseQuotedPair(&$p, &$quoted_pair)
	{
		$quoted_pair = null;
		$v = $this->v;
		$l = strlen($v);
		if ($p+1 < $l && !strcmp($v[$p], '\\') && gettype(strchr("\r\n\0", $c = $v[$p + 1])) != 'string' && ord($c)<128)
		{
			$quoted_pair = $c;
			$p += 2;
		}
		return (1);
	}

	function ParseCContent(&$p, &$c_content)
	{
		$c_content = null;
		$c = $p;
		if (!$this->ParseQuotedPair($c, $content))
		{
			return (0);
		}
		if (!isset($content))
		{
			if (!$this->ParseCText($c, $content))
			{
				return (0);
			}
			if (!isset($content))
			{
				if (!$this->ParseComment($c, $content))
				{
					return (0);
				}
				if (!isset($content))
				{
					return (1);
				}
			}
		}
		$c_content = $content;
		$p = $c;
		return (1);
	}

	function SkipWhiteSpace(&$p)
	{
		$v = $this->v;
		$l = strlen($v);
		for (; $p<$l; ++$p)
		{
			switch ($v[$p])
			{
				case ' ':
				case "\n":
				case "\r":
				case "\t":
				break;
				default:
					return (1);
			}
		}
		return (1);
	}

	function ParseComment(&$p, &$comment)
	{
		$comment = null;
		$v = $this->v;
		$l = strlen($v);
		$c = $p;
		if ($c >= $l || strcmp($v[$c], '('))
		{
			return (1);
		}
		++$c;
		while ($c < $l)
		{
			if (!$this->SkipWhiteSpace($c))
			{
				return (0);
			}
			if (!$this->ParseCContent($c, $c_content))
			{
				return (0);
			}
			if (!isset($c_content))
			{
				break;
			}
		}
		if (!$this->SkipWhiteSpace($c))
		{
			return (0);
		}
		if ($c >= $l || strcmp($v[$c], ')'))
		{
			return (1);
		}
		++$c;
		$comment = substr($v, $p, $c - $p);
		$p = $c;
		return (1);
	}

	function SkipCommentGetWhiteSpace(&$p, &$space)
	{
		$v = $this->v;
		$l = strlen($v);
		for ($space = ''; $p < $l;)
		{
			switch ($w = $v[$p])
			{
				case ' ':
				case "\n":
				case "\r":
				case "\t":
					++$p;
					$space .= $w;
					break;
				case '(':
					if (!$this->ParseComment($p, $comment))
					{
						return (0);
					}
				default:
					return (1);
			}
		}
		return (1);
	}

	function SkipCommentWhiteSpace(&$p)
	{
		$v = $this->v;
		$l = strlen($v);
		while ($p < $l)
		{
			switch ($w = $v[$p])
			{
				case ' ':
				case "\n":
				case "\r":
				case "\t":
					++$p;
				break;
				case '(':
					if (!$this->ParseComment($p, $comment))
					{
						return (0);
					}
				default:
					return (1);
			}
		}
		return (1);
	}

	function ParseQContent(&$p, &$q_content)
	{
		$q_content = null;
		$q = $p;
		if (!$this->ParseQuotedPair($q, $content))
		{
			return (0);
		}
		if (!isset($content))
		{
			if (!$this->ParseQText($q, $content))
			{
				return (0);
			}
			if (!isset($content))
			{
				return (1);
			}
		}
		$q_content = $content;
		$p = $q;
		return (1);
	}

	function ParseAtom(&$p, &$atom, $dot)
	{
		$atom = null;
		$v = $this->v;
		$l = strlen($v);
		$a = $p;
		if (!$this->SkipCommentGetWhiteSpace($a, $space))
		{
			return (0);
		}
		$match = '/^([-'.($dot ? '.' : '').'A-Za-z0-9!#$%&\'*+\\/=?^_`{|}~]+)/';
		for ($s = $a; $a < $l;)
		{
			if (preg_match($match, substr($this->v, $a), $m))
			{
				$a += strlen($m[1]);
			}
			else if (ord($v[$a]) < 128)
			{
				break;
			}
			else if (!$this->SetPositionedWarning('it was used an unencoded 8 bit character', $a))
			{
				return (0);
			}
			else
			{
				++$a;
			}
		}
		if ($s == $a)
		{
			return (1);
		}
		$atom = $space.substr($this->v, $s, $a - $s);
		if (!$this->SkipCommentGetWhiteSpace($a, $space))
		{
			return (0);
		}
		$atom .= $space;
		$p = $a;
		return (1);
	}

	function ParseQuotedString(&$p, &$quoted_string)
	{
		$quoted_string = null;
		$v = $this->v;
		$l = strlen($v);
		$s = $p;
		if (!$this->SkipCommentWhiteSpace($s))
		{
			return (0);
		}
		if ($s >= $l || strcmp($v[$s], '"'))
		{
			return (1);
		}
		++$s;
		for ($string = ''; $s < $l;)
		{
			$w = $s;
			if (!$this->SkipWhiteSpace($s))
			{
				return (0);
			}
			if ($w != $s)
			{
				$string .= substr($v, $w, $s - $w);
			}
			if (!$this->ParseQContent($s, $q_content))
			{
				return (0);
			}
			if (!isset($q_content))
			{
				break;
			}
			$string .= $q_content;
		}
			$w = $s;
		if (!$this->SkipWhiteSpace($s))
		{
			return (0);
		}
		if ($w != $s)
		{
			$string .= substr($v, $w, $s - $w);
		}
		if ($s >= $l || strcmp($v[$s], '"'))
		{
			return (1);
		}
		++$s;
		if (!$this->SkipCommentWhiteSpace($s))
		{
			return (0);
		}
		$quoted_string = $string;
		$p = $s;
		return (1);
	}

	function ParseWord(&$p, &$word, &$escape)
	{
		$word = null;
		if (!$this->ParseQuotedString($p, $word))
		{
			return (0);
		}
		if (isset($word))
		{
			return (1);
		}
		for ($w = '';;)
		{
			$last = $w;
			if (!$this->ParseAtom($p, $atom, 0))
			{
				return (0);
			}

			if (isset($atom))
			{
				$w .= $atom;
			}
			if ($this->ignore_syntax_errors)
			{
				$e = $p;
				if (!$this->ParseQuotedPair($p, $quoted_pair))
				{
					return(0);
				}
				if (isset($quoted_pair))
				{
					$w .= $quoted_pair;
					if (!isset($escape))
					{
						$escape = $e;
					}
				}
			}
			if ($last === $w)
			{
				break;
			}
		}
		if (strlen($w))
		{
			$word = $w;
		}
		return (1);
	}

	function ParseObsPhrase(&$p, &$obs_phrase, &$escape)
	{
		$obs_phrase = null;
		$v = $this->v;
		$l = strlen($v);
		$ph = $p;
		if (!$this->ParseWord($ph, $word, $escape))
		{
			return (0);
		}
		$string = $word;
		for (;;)
		{
			if (!$this->ParseWord($ph, $word, $escape))
			{
				return (0);
			}
			if (isset($word))
			{
				$string .= $word;
				continue;
			}
			$w = $ph;
			if (!$this->SkipCommentGetWhiteSpace($ph, $space))
			{
				return (0);
			}
			if ($w != $ph)
			{
				$string .= $space;
				continue;
			}
			if ($ph >= $l || strcmp($v[$ph], '.'))
			{
				break;
			}
			$string .= '.';
			++$ph;
		}
		$obs_phrase = $string;
		$p = $ph;
		return (1);
	}

	function ParsePhrase(&$p, &$phrase, $escape)
	{
		$phrase = null;
		if (!$this->ParseObsPhrase($p, $phrase, $escape))
		{
			return (0);
		}
		if (isset($phrase))
		{
			return (1);
		}
		$ph = $p;
		if (!$this->ParseWord($ph, $word, $escape))
		{
			return (0);
		}
		$string = $word;
		for (;;)
		{
			if (!$this->ParseWord($ph, $word, $escape))
			{
				return (0);
			}
			if (!isset($word))
			{
				break;
			}
			$string .= $word;
		}
		$phrase = $string;
		$p = $ph;
		return (1);
	}

	function ParseAddrSpec(&$p, &$addr_spec)
	{
		$addr_spec = null;
		$v = $this->v;
		$l = strlen($v);
		$a = $p;
		if (!$this->ParseQuotedString($a, $local_part))
		{
			return (0);
		}
		if (!isset($local_part))
		{
			if (!$this->ParseAtom($a, $local_part, 1))
			{
				return (0);
			}
			$local_part = trim($local_part);
		}
		if ($a >= $l || strcmp($v[$a], '@'))
		{
			return (1);
		}
		++$a;
		if (!$this->ParseAtom($a, $domain, 1))
		{
			return (0);
		}
		if (!isset($domain))
		{
			return (1);
		}
		$addr_spec = $local_part . '@' . trim($domain);
		$p = $a;
		return (1);
	}

	function ParseAngleAddr(&$p, &$addr)
	{
		$addr = null;
		$v = $this->v;
		$l = strlen($v);
		$a = $p;
		if (!$this->SkipCommentWhiteSpace($a))
		{
			return (0);
		}
		if ($a >= $l || strcmp($v[$a], '<'))
		{
			return (1);
		}
		++$a;
		if (!$this->ParseAddrSpec($a, $addr_spec))
		{
			return (0);
		}
		if ($a >= $l || strcmp($v[$a], '>'))
		{
			return (1);
		}
		++$a;
		if (!$this->SkipCommentWhiteSpace($a))
		{
			return (0);
		}
		$addr = $addr_spec;
		$p = $a;
		return (1);
	}

	function ParseName(&$p, &$address)
	{
		$address = $escape = null;
		$a = $p;
		if (!$this->ParsePhrase($a, $display_name))
		{
			return (0);
		}
		if (isset($display_name))
		{
			if (isset($escape) && !$this->SetPositionedWarning('it was used an escape character outside a quoted value', $escape))
			{
				return(0);
			}
			if (!$this->QDecode($p, $display_name, $encoding))
			{
				return (0);
			}
			$address['name'] = trim($display_name);
			if (isset($encoding))
			{
				$address['encoding'] = $encoding;
			}
		}
		$p = $a;
		return (1);
	}

	function ParseNameAddr(&$p, &$address)
	{
		$address = $escape = null;
		$a = $p;
		if (!$this->ParsePhrase($a, $display_name, $escape))
		{
			return (0);
		}
		if (!$this->ParseAngleAddr($a, $addr))
		{
			return (0);
		}
		if (!isset($addr))
		{
			return (1);
		}
		$address = array('address'=>$addr);
		if (isset($display_name))
		{
			if (isset($escape) && !$this->SetPositionedWarning('it was used an escape character outside a quoted value', $escape))
			{
				return(0);
			}
			if (!$this->QDecode($p, $display_name, $encoding))
			{
				return (0);
			}
			$address['name'] = trim($display_name);
			if (isset($encoding))
			{
				$address['encoding'] = $encoding;
			}
		}
		$p = $a;
		return (1);
	}

	function ParseAddrNameAddr(&$p, &$address)
	{
		$address = null;
		$a = $p;
		if (!$this->ParseAddrSpec($a, $display_name))
		{
			return (0);
		}
		if (!isset($display_name))
		{
			return (1);
		}
		if (!$this->ParseAngleAddr($a, $addr))
		{
			return (0);
		}
		if (!isset($addr))
		{
			return (1);
		}
		if (!$this->QDecode($p, $display_name, $encoding))
		{
			return (0);
		}
		$address = array(
			'address'=>$addr,
			'name' => trim($display_name)
		);
		if (isset($encoding))
		{
			$address['encoding'] = $encoding;
		}
		$p = $a;
		return (1);
	}

	function ParseMailbox(&$p, &$address)
	{
		$address = null;
		if ($this->ignore_syntax_errors)
		{
			$a = $p;
			if (!$this->ParseAddrNameAddr($p, $address))
			{
				return (0);
			}
			if (isset($address))
			{
				return ($this->SetPositionedWarning('it was specified an unquoted address as name', $a));
			}
		}
		if (!$this->ParseNameAddr($p, $address))
		{
			return (0);
		}
		if (isset($address))
		{
			return (1);
		}
		if (!$this->ParseAddrSpec($p, $addr_spec))
		{
			return (0);
		}
		if (isset($addr_spec))
		{
			$address = array('address'=>$addr_spec);
			return (1);
		}
		$a = $p;
		if ($this->ignore_syntax_errors && $this->ParseName($p, $address) && isset($address))
		{
			return ($this->SetPositionedWarning('it was specified a name without an address', $a));
		}
		return (1);
	}

	function ParseMailboxGroup(&$p, &$mailbox_group)
	{
		$v = $this->v;
		$l = strlen($v);
		$g = $p;
		if (!$this->ParseMailbox($g, $address))
		{
			return (0);
		}
		if (!isset($address))
		{
			return (1);
		}
		$addresses = array($address);
		while ($g < $l)
		{
			if (strcmp($v[$g], ','))
			{
				break;
			}
			++$g;
			if (!$this->ParseMailbox($g, $address))
			{
				return (0);
			}
			if (!isset($address))
			{
				return (1);
			}
			$addresses[] = $address;
		}
		$mailbox_group = $addresses;
		$p = $g;
		return (1);
	}

	function ParseGroup(&$p, &$address)
	{
		$address = $escape = null;
		$v = $this->v;
		$l = strlen($v);
		$g = $p;
		if (!$this->ParsePhrase($g, $display_name, $escape))
		{
			return (0);
		}
		if (!isset($display_name) || $g >= $l || strcmp($v[$g], ':'))
		{
			return (1);
		}
		++$g;
		if (!$this->ParseMailboxGroup($g, $mailbox_group))
		{
			return (0);
		}
		if (!isset($mailbox_group))
		{
			if (!$this->SkipCommentWhiteSpace($g))
			{
				return (0);
			}
			$mailbox_group = [];
		}
		if ($g >= $l || strcmp($v[$g], ';'))
		{
			return (1);
		}
		$c = ++$g;
		if ($this->SkipCommentWhiteSpace($g) && $g > $c && !$this->SetPositionedWarning('it were used invalid comments after a group of addresses', $c))
		{
			return (0);
		}
		if (isset($escape) && !$this->SetPositionedWarning('it was used an escape character outside a quoted value', $escape))
		{
			return(0);
		}
		if (!$this->QDecode($p, $display_name, $encoding))
		{
			return (0);
		}
		$address = array(
			'name'=>$display_name,
			'group'=>$mailbox_group
		);
		if (isset($encoding))
		{
			$address['encoding'] = $encoding;
		}
		$p = $g;
		return (1);
	}

	function ParseAddress(&$p, &$address)
	{
		$address = null;
		if (!$this->ParseGroup($p, $address))
		{
			return (0);
		}
		if (!isset($address))
		{
			if (!$this->ParseMailbox($p, $address))
			{
				return (0);
			}
		}
		return (1);
	}

	/* Public functions */

	function ParseAddressList($value, &$addresses)
	{
		$this->warnings = [];
		$addresses = [];
		$this->v = $v = $value;
		$l = strlen($v);
		$p = 0;
		if (!$this->ParseAddress($p, $address))
		{
			return (0);
		}
		if (!isset($address))
		{
			return ($this->SetPositionedError('it was not specified a valid address', $p));
		}
		$addresses[] = $address;
		while ($p < $l)
		{
			if (strcmp($v[$p], ',') && !$this->SetPositionedWarning('multiple addresses must be separated by commas: ', $p))
			{
				return (0);
			}
			++$p;
			if (!$this->ParseAddress($p, $address))
			{
				return (0);
			}
			if (!isset($address))
			{
				return ($this->SetPositionedError('it was not specified a valid address after comma', $p));
			}
			$addresses[] = $address;
		}
		return (1);
	}
}
