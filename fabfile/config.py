from os import environ, path

from dotenv import load_dotenv

# ! Config

basepath = path.abspath(path.dirname(__file__)+'/../')

env_filename = '.env'
load_dotenv(path.join(basepath, env_filename))


CONFIG = {
    'env_file': env_filename,
    'base_path': basepath
}
