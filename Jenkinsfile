pipeline {
    agent any
    stages {

        stage('One') {
            steps {
                echo 'Hi, this is Sammy from masgeek'
            }
        }
        stage('Two') {
            steps {
                input('Do you want to proceed?')
            }
        }
        stage('Three') {
            when {
                not {
                    branch "master"
                }
            }
            steps {
                echo "Hello not master"
            }
        }
        stage('Four') {
            parallel {
                stage('Unit Test') {
                    steps {
                        echo "Running the unit test..."
                    }
                }
                stage('Integration test') {
                    agent {
                        docker {
                            reuseNode true
                            image 'ubuntu'
                        }
                    }
                    steps {
                        echo "Running the integration test..."
                    }
                }
            }
        }


    }
}