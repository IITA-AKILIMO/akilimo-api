pipeline {
    agent any

     environment {
            VERSION_NUMBER = '3'
            MINOR_NUMBER    = '1'
        }

    stages {
        stage('Chmod permissions') {
            steps {
                echo "Chmod permissions"
                sh 'chmod +x ./gradlew'
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
            when {
                not {
                    anyOf {
                        branch 'master';
                        branch 'develop'
                    }
                }
            }
            steps {
                echo "Running tests"
                sh './gradlew build assemble'
            }
        }
        stage('Build latest') {
            when {
                not {
                    anyOf {
                        branch 'master';
                        branch 'develop'
                    }
                }
            }
            steps {
                echo "Building docker image"
                sh "docker build -f Dockerfile -t iita/acai-akilimo-api:${BUILD_NUMBER} ."
            }
        }

        stage('Archive artifacts'){
            when{
                not{
                    anyOf{
                        branch "master";
                        branch "develop"
                    }
                }
            }
            steps {
                archiveArtifacts artifacts: 'build/libs/akilimo*.jar'
            }
        }

    }
}