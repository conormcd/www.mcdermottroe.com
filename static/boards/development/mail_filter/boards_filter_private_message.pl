#!/usr/bin/perl

use strict;
use warnings;

use Digest::SHA qw(sha1_hex);
use Getopt::Long;
use MIME::Parser;
use Text::Wrap;

# Options and defaults
my $realname_file;
my $my_boards_name;
my $my_real_name;
GetOptions(
	"names-file=s" => \$realname_file,
	"my-boards-name=s" => \$my_boards_name,
	"my-real-name=s" => \$my_real_name
);

# If provided, the first argument on the command line is the path to a file
# containing a mapping between boards.ie usernames and real names.
my %realname;
if ($realname_file) {
	if (-r $realname_file) {
		if (open(MAP, $realname_file)) {
			while (<MAP>) {
				chomp;
				my($bname, $rname) = split(/ => /o);
				$realname{$bname} = $rname;
			}
			close(MAP);
		}
	}
}

# Any wrapping we do will be to 72 columns
$Text::Wrap::columns = 72;

# Create the mail parser
my $parser = new MIME::Parser;
$parser->output_to_core(1);

# Read the mail
my @mail;
while (<STDIN>) {
	push @mail, $_;
}

# Parse the mail
my $mail = $parser->parse_data(\@mail);

# Get the headers and decide what to do
my $head = $mail->head;
if ($head->get('Subject') =~ /^New Private Message at boards\.ie$/o) {
	# Read the body and get the PM details
	my $body = $mail->bodyhandle;
	my $pm_sender;
	my $pm_subject;
	my @pm_text;
	$body->as_string =~ /You\s*have\s*received\s*a\s*new\s*private\s*message\s*at\s*boards\.ie\s*from\s*(.*?),\s*entitled "(.*?)"\./mso;
	$pm_sender = &realname($1);
	$pm_subject = $2;
	$body->as_string =~ /This is the message that was sent:.*?\*{10,}(.*?)\*{10,}/mso;
	@pm_text = split(/[\r\n]/o, $1);

	# Quote and wrap the way e-mails should be done
	my $quoter = "";
	for (my $i = 0; $i < @pm_text; $i++) {
		if ($pm_text[$i] =~ /^---Quote \(Originally by (.*?)\)---$/o) {
			if ($quoter) {
				$pm_text[$i] = $quoter . " " . &realname($1) . ":\n";
			} else {
				$pm_text[$i] = &realname($1) . ":\n";
			}
			$quoter .= ">";
		} elsif ($pm_text[$i] =~ /^---Quote---$/o) {
			$pm_text[$i] = "$quoter\n";
			$quoter .= ">";
		} elsif ($pm_text[$i] =~ /^---End Quote---$/o) {
			$pm_text[$i] = "$quoter\n";
			$quoter =~ s/>$//o;
		} elsif ($pm_text[$i] eq "") {
			if ($quoter) {
				$pm_text[$i] = "$quoter\n";
			} else {
				$pm_text[$i] = "\n";
			}
		} else {
			if ($quoter) {
				$pm_text[$i] = wrap("$quoter ", "$quoter ", "$pm_text[$i]\n");
			} else {
				$pm_text[$i] = wrap("", "", "$pm_text[$i]\n");
			}
		}
	}

	# Make sure we have everything we need
	unless ($pm_sender && $pm_subject && @pm_text && $my_real_name && $my_boards_name) {
		&bail;
	}
	
	# Re-make the body
	$body = new MIME::Body::InCore(\@pm_text);

	# Fiddle the header
	$head->replace('From', $pm_sender);
	$head->replace('Subject', $pm_subject);
	$head->replace('To', "$my_real_name <$my_boards_name\@boards>");
	$head->add('X-Boards-Filter-Message-Type', 'PM');

	# Re-make the entity for the new mail
	$mail->head($head);
	$mail->bodyhandle($body);

	$mail->print();
} elsif ($head->get('Subject') =~ /^Reported post from boards\.ie/io) {
	# Fiddle the header
	$head->add('X-Boards-Filter-Message-Type', 'RP');

	# Re-make the entity for the new mail
	$mail->head($head);
	$mail->bodyhandle($mail->bodyhandle);

	$mail->print();
} elsif ($head->get('Subject') =~ /^Reported Visitor Message from boards\.ie$/o) {
	# Fiddle the header
	$head->add('X-Boards-Filter-Message-Type', 'RVM');

	# Re-make the entity for the new mail
	$mail->head($head);
	$mail->bodyhandle($mail->bodyhandle);

	$mail->print();
} else {
	&bail;
}

# All errors should jsut cause the original mail to be printed.
sub bail {
	foreach (@mail) {
		print;
	}
	exit 0;
}

sub realname {
	my $boardsname = shift;
	return "$realname{$boardsname} <$boardsname\@boards>" if $realname{$boardsname};
	return "$boardsname <$boardsname\@boards>";
}
