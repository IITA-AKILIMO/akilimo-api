pipeline {
    agent any
    stages {
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
        stage('Clean code base') {
            steps {
                echo "Cleaning project"
                sh './gradlew clean'
            }
        }

        stage('Run tests and check coverage') {
            steps {
                echo "Running tests"
                sh './gradlew check'
            }
        }
        stage('Build binary files for release branches') {
            steps {
                when {
                    not {
                        anyOf {
                            branch 'master';
                            branch 'develop'
                        }
                    }
                }
                echo "Running tests"
                sh './gradlew build assemble'
            }
        }
    }
}