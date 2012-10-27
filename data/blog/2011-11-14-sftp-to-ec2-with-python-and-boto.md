# SFTP to EC2 with Python and Boto

Today I wanted to automate the upload of some code to a new Amazon EC2
instance. I've been scripting the rest of the job using 
[Boto](https://github.com/boto/boto) but when I was lazily looking for an
example of how to do SFTP with Boto there wasn't anything obvious in the first
few pages of Google's results. So, here's the snippet for any other lazy
coders out there:

    {{lang:python}}
    import boto.manage.cmdshell

    def upload_file(instance, key, username, local_filepath, remote_filepath):
        """
        Upload a file to a remote directory using SFTP. All parameters except
        for "instance" are strings. The instance parameter should be a
        boto.ec2.instance.Instance object.

        instance        An EC2 instance to upload the files to.
        key             The file path for a valid SSH key which can be used to
                        log in to the EC2 machine.
        username        The username to log in as.
        local_filepath  The path to the file to upload.
        remote_filepath The path where the file should be uploaded to.
        """
        ssh_client = boto.manage.cmdshell.sshclient_from_instance(
            instance,
            key,
            user_name=username
        )
        ssh_client.put_file(local_filepath, remote_filepath)

Boto depends on [paramiko](https://github.com/robey/paramiko/) to handle the
SSH parts, so you'll need that installed too.
