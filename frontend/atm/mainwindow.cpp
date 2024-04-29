#include "mainwindow.h"
#include "ui_mainwindow.h"
#include <chrono>
#include <thread>


MainWindow::MainWindow(QWidget *parent)
    : QMainWindow(parent)
    , ui(new Ui::MainWindow)
{
    ui->setupUi(this);
    welcome = new Welcome(this);
    pinui = new PinUI(this);
    cardselect = new CardSelect(this);
    accountinfo = new AccountInfo(this);
    withdraw = new Withdraw(this);

    login = new LoginDLL(this);
    rfid = new RFID(this);

    logoutTimer = new QTimer;
    startWidgetTimer = new QTimer;

    logoutTimer->setSingleShot(true);

    ui->viewer->addWidget(welcome);
    ui->viewer->addWidget(pinui);
    ui->viewer->addWidget(cardselect);
    ui->viewer->addWidget(accountinfo);
    ui->viewer->addWidget(withdraw);

    ui->viewer->setCurrentWidget(welcome);

    // play background music
    qDebug()<<"Mediaplayer created!!";
    audioOutput = new QAudioOutput;
    music = new QMediaPlayer;
    music->setLoops(-1);
    music->setAudioOutput(audioOutput);
    music->setSource(QUrl("qrc:/sounds/ElevatorMusic.mp3"));
    qDebug()<<music->errorString();
    audioOutput->setVolume(50);
    music->play();

    connect(
        welcome,
        SIGNAL(start()),
        this,
        SLOT(start())
    );

    connect(
        rfid,
        SIGNAL(cardData(QString)),
        this,
        SLOT(handleData(QString))
    );

    connect(
        pinui,
        SIGNAL(sendPinNumber(QString)),
        this,
        SLOT(handleData(QString))
    );

    connect(
        pinui,
        SIGNAL(pinCancel()),
        this,
        SLOT(logout())
    );

    connect(
        login,
        SIGNAL(loginStatus(LoginDLL::LoginStatus)),
        this,
        SLOT(checkLoginStatus(LoginDLL::LoginStatus))
    );

    connect(
        login,
        SIGNAL(accountsInfo(QJsonArray)),
        this,
        SLOT(handleAccounts(QJsonArray))
    );

    connect(
        login,
        SIGNAL(withdrawalsInfo(QJsonArray)),
        accountinfo,
        SLOT(getWithdrawalsInfo(QJsonArray)),
        Qt::QueuedConnection
    );

    connect(
        login,
        SIGNAL(nostotapahtumaInfo(QString)),
        accountinfo,
        SLOT(withdrawError(QString)),
        Qt::QueuedConnection
    );

    connect(
        login,
        SIGNAL(customerInfo(QJsonArray)),
        accountinfo,
        SLOT(handleCustomerInfo(QJsonArray)),
        Qt::QueuedConnection
    );

    connect(
        login,
        SIGNAL(withdrawalDone()),
        this,
        SLOT(refetchWithdrawals())
    );

    connect(
        login,
        SIGNAL(sendBalance(QString)),
        accountinfo,
        SLOT(updateBalance(QString))
    );

    connect(
        cardselect,
        SIGNAL(selectedAccount(CardSelect::AccountType)),
        this,
        SLOT(handleAccountSelect(CardSelect::AccountType))
    );

    connect(
        accountinfo,
        SIGNAL(withdrawSignal()),
        this,
        SLOT(openWithdrawWidget())
    );

    connect(
        accountinfo,
        SIGNAL(logout()),
        this,
        SLOT(logout())
    );

    connect(
        withdraw,
        SIGNAL(sendWithdrawal(QString)),
        this,
        SLOT(handleWithdrawal(QString))
    );

    connect(
        logoutTimer,
        SIGNAL(timeout()),
        this,
        SLOT(logout()),
        Qt::UniqueConnection
    );

    connect(
        startWidgetTimer,
        SIGNAL(timeout()),
        this,
        SLOT(returnToStart()),
        Qt::UniqueConnection
    );
}

MainWindow::~MainWindow()
{
    music->stop();
    delete ui;
    delete welcome;
    delete pinui;
    delete cardselect;
    delete accountinfo;
    delete withdraw;
    delete logoutTimer;

    delete music;
    delete audioOutput;

    delete rfid;
    delete login;
}

void MainWindow::handleAccounts(QJsonArray accounts)
{
    qDebug() << "Processing card information";
    QJsonObject account;

    qsizetype len = accounts.size();
    if (len == 1) {
        qDebug() << "Single account detected..";
        account = (accounts.at(0).toObject());

        QString creditamt = account["credit"].toString();
        QString type;

        accountid = QString::number(account["idaccount"].toInt());
        login->setAccountId(accountid);
        login->getCustomerInfo();

        if (creditamt == "" || creditamt == "0.00" || creditamt == "0") {
            type = "Debit";
        } else {
            type = "Credit (" + account["credit"].toString() + ")";
        }

        accountinfo->setInfo(
            account,
            cardNum,
            type
        );

        logoutTimer->start(3 * 60 * 1000);
        ui->viewer->setCurrentWidget(accountinfo);
        return;

    } else {
        logoutTimer->start(10000);
        qDebug() << "Double card detected. Splitting..";

        for (qsizetype i = 0; i < len; i++) {
            account = accounts.at(i).toObject();
            QString creditamt = account["credit"].toString();

            if (creditamt == "" || creditamt == "0.00" || creditamt == "0") {
                debit = account;
                qDebug() << "Debit set to: " << debit;

            } else {
                credit = account;
                qDebug() << "Credit set to: " << credit;

            }
        }

        ui->viewer->setCurrentWidget(cardselect);

    }
}

void MainWindow::logout()
{
    login->logout();
    logoutTimer->stop();
    cardselect->zeroize();
    accountinfo->zeroize();
    debit = credit = QJsonObject();
    pin = cardNum = "";
    welcome->setLoggedOut();
    startWidgetTimer->setSingleShot(true);
    startWidgetTimer->start(2000);
    ui->viewer->setCurrentWidget(welcome);
}

void MainWindow::returnToStart()
{
    welcome->setStart();
    rfid->closeReader();
    // ui->viewer->setCurrentWidget(welcome);
}

void MainWindow::start()
{
    if (!rfid->setReader()) {
        welcome->setCardReaderError();
    }
    startWidgetTimer->start(10000);
}

void MainWindow::checkLoginStatus(LoginDLL::LoginStatus s)
{
    switch (s) {
        case LoginDLL::LoginStatus::Ok:
            login->getCardInformation();
            break;

        case LoginDLL::LoginStatus::InvalidCredentials:
            if (tries < 3) {
                tries++;
                pinui->setAlert(true);
                ui->viewer->setCurrentWidget(pinui);

            } else {
                logout();
                login->lockCard();
                pinui->setAlert(false);
                ui->viewer->setCurrentWidget(welcome);

            }
            break;

        case LoginDLL::LoginStatus::ConnectionError:
            // TODO: Show connection error message
            break;

        default:
            break;
    }
}

void MainWindow::handleAccountSelect(CardSelect::AccountType t)
{
    switch (t) {
    case CardSelect::AccountType::Debit:
        accountinfo->setInfo(debit, cardNum, "Debit");
        accountid = QString::number(debit["idaccount"].toInt());
        break;

    case CardSelect::AccountType::Credit:
        accountinfo->setInfo(credit, cardNum, "Credit (" + credit["credit"].toString() + ")");
        accountid = QString::number(credit["idaccount"].toInt());
        break;

    default:
        qDebug() << "Unexpected value as AccountType";
        break;
    }

    login->setAccountId(accountid);
    login->getCustomerInfo();
    accountinfo->timeout->start(30000);
    logoutTimer->start(3 * 60 * 1000);
    ui->viewer->setCurrentWidget(accountinfo);
}

void MainWindow::handleWithdrawal(QString withdrawal)
{
    if (withdrawal != "0") {
        qDebug() << "Withdrawal from account " + accountid + " with sum of " + withdrawal;
        login->nostotapahtuma(accountid, withdrawal);
    }

    accountinfo->timeout->start(10000);
    ui->viewer->setCurrentWidget(accountinfo);
}

void MainWindow::openWithdrawWidget()
{
    ui->viewer->setCurrentWidget(withdraw);
    withdraw->timeout->start(10000);
}

void MainWindow::refetchWithdrawals()
{
    qDebug() << "Refetching balance and withdrawals for account number " + accountid + "..";
    login->getBalance(accountid);
}

void MainWindow::handleData(QString d)
{
    if (sender() == rfid) {
        rfid->closeReader();
        startWidgetTimer->stop();
        cardNum = d;
        pinui->setAlert(false);
        pinui->timeout->start(10000);
        tries = 0;
        ui->viewer->setCurrentWidget(pinui);

    } else if (sender() == pinui) {
        pin = d;
        welcome->setLoggingIn();
        ui->viewer->setCurrentWidget(welcome);
        login->login(cardNum, pin);
    }
}
