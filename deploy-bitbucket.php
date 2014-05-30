<?php
//Path to mirrored repo
$repo_dir = 'path to repo';

//Path to working directories
$master_root_dir = 'path to production directory';
$dev_root_dir = 'path to dev directory';

// Full path to git binary is required if git is not in your PHP user's path. Otherwise just use 'git'.
$git_bin_path = 'git';


// Parse data from Bitbucket hook payload
$payload = json_decode(stripslashes($_POST['payload']));

if (empty($payload->commits))
{

	// When merging and pushing to bitbucket, the commits array will be empty. testing...
	file_put_contents('deploy.log', date('m/d/Y h:i:s a') . " Empty Payload for: " . $branch . " Commit: " . $commit_hash . "\n", FILE_APPEND);

}
else
{

	foreach ($payload->commits as $commit)
	{

		$branch = $commit->branch;

		if ($branch === 'master' || isset($commit->branches) && in_array('master', $commit->branches))
		{

			// Do a git checkout to the web root
			exec('cd ' . $repo_dir . ' && ' . $git_bin_path  . ' fetch');
			exec('cd ' . $repo_dir . ' && GIT_WORK_TREE=' . $master_root_dir . ' ' . $git_bin_path  . ' checkout -f' . $branch);

			// Log the deployment
			$commit_hash = shell_exec('cd ' . $repo_dir . ' && ' . $git_bin_path  . ' rev-parse --short HEAD');
			file_put_contents('deploy.log', date('m/d/Y h:i:s a') . " Deployed branch: " . $branch . " Commit: " . $commit_hash . "\n", FILE_APPEND);

		}
		elseif ($branch === 'dev' || isset($commit->branches) && in_array('dev', $commit->branches))
		{

			// Do a git checkout to the web root
			exec('cd ' . $repo_dir . ' && ' . $git_bin_path  . ' fetch');
			exec('cd ' . $repo_dir . ' && GIT_WORK_TREE=' . $dev_root_dir . ' ' . $git_bin_path  . ' checkout -f' . $branch);

			// Log the deployment
			$commit_hash = shell_exec('cd ' . $repo_dir . ' && ' . $git_bin_path  . ' rev-parse --short HEAD');
			file_put_contents('deploy.log', date('m/d/Y h:i:s a') . " Deployed branch: " . $branch . " Commit: " . $commit_hash . "\n", FILE_APPEND);

		}
		else
		{

			// for debugging eventually remove
			file_put_contents('deploy.log', date('m/d/Y h:i:s a') . " Error with: " . $branch . " Commit: " . $commit_hash . "\n", FILE_APPEND);
			break;

		}
	}
}

?>