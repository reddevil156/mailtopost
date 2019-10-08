<?php
/**
*
* @package Mail to Post Extension
* @copyright (c) 2019 david63
* @license GNU General Public License, version 2 (GPL-2.0)
*
* pop3_stream.php
*
* @(#) $Header: /opt2/ena/metal/pop3/pop3.php,v 1.24 2014/01/27 10:46:48 mlemos Exp $
*
**/

namespace david63\mailtopost\pop3mail;

class pop3_stream
{
	var $opened = 0;
	var $report_errors = 1;
	var $read = 0;
	var $buffer = "";
	var $end_of_message = 1;
	var $previous_connection = 0;
	var $pop3;

	function SetError($error)
	{
		if ($this->report_errors)
		{
			trigger_error($error);
		}
		return (false);
	}

	function ParsePath($path, &$url)
	{
		if (!$this->previous_connection)
		{
			if (isset($url["host"]))
			{
				$this->pop3->hostname=$url["host"];
			}
			if (isset($url["port"]))
			{
				$this->pop3->port=intval($url["port"]);
			}
			if (isset($url["scheme"]) && !strcmp($url["scheme"],"pop3s"))
			{
				$this->pop3->tls=1;
			}
			if (!isset($url["user"]))
			{
				return ($this->SetError("it was not specified a valid POP3 user"));
			}
			if (!isset($url["pass"]))
			{
				return ($this->SetError("it was not specified a valid POP3 password"));
			}
			if (!isset($url["path"]))
			{
				return ($this->SetError("it was not specified a valid mailbox path"));
			}
		}
		if (isset($url["query"]))
		{
			parse_str($url["query"],$query);
			if (isset($query["debug"]))
			{
				$this->pop3->debug = intval($query["debug"]);
			}
			if (isset($query["html_debug"]))
			{
				$this->pop3->html_debug = intval($query["html_debug"]);
			}
			if (!$this->previous_connection)
			{
				if (isset($query["tls"]))
				{
					$this->pop3->tls = intval($query["tls"]);
				}
				if (isset($query["realm"]))
				{
					$this->pop3->realm = UrlDecode($query["realm"]);
				}
				if (isset($query["workstation"]))
				{
					$this->pop3->workstation = UrlDecode($query["workstation"]);
				}
				if (isset($query["authentication_mechanism"]))
				{
					$this->pop3->realm = UrlDecode($query["authentication_mechanism"]);
				}
			}
			if (isset($query["quit_handshake"]))
			{
				$this->pop3->quit_handshake = intval($query["quit_handshake"]);
			}
		}
		return (true);
	}

	function stream_open($path, $mode, $options, &$opened_path)
	{
// ***************************
		global $config;
// ***************************
		$this->report_errors = (($options & STREAM_REPORT_ERRORS) != 0);
		if (strcmp($mode, "r"))
		{
			return ($this->SetError("the message can only be opened for reading"));
		}
		$url=parse_url($path);
		$host = $url['host'];
// ***************************
		$pop3_class = new \david63\mailtopost\pop3mail\pop3($config);
// ***************************
		//$pop3_class = new pop3_class();
		$pop3 = &$pop3_class->SetConnection(0, $host, $this->pop3);
		if (isset($pop3))
		{
			$this->pop3 = &$pop3;
			$this->previous_connection = 1;
		}
		else
		{
			$this->pop3=new pop3_class;
		}
		if (!$this->ParsePath($path, $url))
		{
			return (false);
		}
		$message=substr($url["path"],1);
		if (strcmp(intval($message), $message) || $message<=0)
		{
			return ($this->SetError("it was not specified a valid message to retrieve"));
		}
		if (!$this->previous_connection)
		{
			if (strlen($error=$this->pop3->Open()))
			{
				return ($this->SetError($error));
			}
			$this->opened = 1;
			$apop = (isset($url["query"]["apop"]) ? intval($url["query"]["apop"]) : 0);
			if (strlen($error=$this->pop3->Login(UrlDecode($url["user"]), UrlDecode($url["pass"]),$apop)))
			{
				$this->stream_close();
				return ($this->SetError($error));
			}
		}
		if (strlen($error=$this->pop3->OpenMessage($message,-1)))
		{
			$this->stream_close();
			return ($this->SetError($error));
		}
		$this->end_of_message = false;
		if ($options & STREAM_USE_PATH)
		{
			$opened_path=$path;
		}
		$this->read = 0;
		$this->buffer = "";
		return (true);
	}

	function stream_eof()
	{
		if ($this->read==0)
		{
			return (false);
		}
		return ($this->end_of_message);
	}

	function stream_read($count)
	{
		if ($count <= 0)
		{
			return ($this->SetError("it was not specified a valid length of the message to read"));
		}
		if ($this->end_of_message)
		{
			return ("");
		}
		if (strlen($error=$this->pop3->GetMessage($count, $read, $this->end_of_message)))
		{
			return ($this->SetError($error));
		}
		$this->read += strlen($read);
		return ($read);
	}

	function stream_close()
	{
		while (!$this->end_of_message)
		{
			$this->stream_read(8000);
		}
		if ($this->opened)
		{
			$this->pop3->Close();
			$this->opened = 0;
		}
	}
}
